<?php

namespace App\Modules\OrdensServico\Services;

use App\Modules\OrdensServico\Models\OrdemServico;
use App\Modules\OrdensServico\Models\OrdemServicoItem;
use App\Modules\OrdensServico\DTOs\OrdemServicoDTO;
use App\Modules\Produtos\Models\Item;
use App\Modules\Produtos\Services\ItemService;
use Illuminate\Support\Facades\DB;
use App\Modules\Financeiro\Services\LancamentoFinanceiroService;

class OrdemServicoService
{
    // Atualizar o construtor:
public function __construct(
    private ItemService $itemService,
    private LancamentoFinanceiroService $financeiroService
) {}

    public function criarOS(OrdemServicoDTO $dto): OrdemServico
    {
        return DB::transaction(function () use ($dto) {
            $numeroOs = OsNumeroService::gerarProximoNumero($dto->empresaId);

            $os = OrdemServico::create([
                'empresa_id' => $dto->empresaId,
                'cliente_id' => $dto->clienteId,
                'tecnico_id' => $dto->tecnicoId,
                'numero_os' => $numeroOs,
                'status' => $dto->status,
                'data_abertura' => now(),
                'previsao_entrega' => $dto->previsaoEntrega,
                'defeito_relatado' => $dto->defeitoRelatado,
                'laudo_tecnico' => $dto->laudoTecnico,
                'observacoes_internas' => $dto->observacoesInternas,
                'termos_garantia' => $dto->termosGarantia,
                'valor_desconto' => $dto->valorDesconto,
            ]);

            $totalServicos = 0.00;
            $totalProdutos = 0.00;

            foreach ($dto->itens as $itemRequest) {
                $itemCadastrado = Item::where('id', $itemRequest['item_id'])
                    ->where('empresa_id', $dto->empresaId)
                    ->firstOrFail();

                $quantidade = (float) $itemRequest['quantidade'];
                $valorUnitario = (float) ($itemRequest['valor_unitario'] ?? $itemCadastrado->preco_venda);
                $subtotal = $quantidade * $valorUnitario;

                if ($itemCadastrado->tipo === 'S') {
                    $totalServicos += $subtotal;
                } else {
                    $totalProdutos += $subtotal;
                }

                OrdemServicoItem::create([
                    'ordem_servico_id' => $os->id,
                    'item_id' => $itemCadastrado->id,
                    'descricao_item' => $itemCadastrado->nome,
                    'quantidade' => $quantidade,
                    'valor_unitario' => $valorUnitario,
                    'valor_subtotal' => $subtotal,
                ]);
            }

            $valorTotal = ($totalServicos + $totalProdutos) - $dto->valorDesconto;

            $os->update([
                'valor_servicos' => $totalServicos,
                'valor_produtos' => $totalProdutos,
                'valor_total' => max(0, $valorTotal),
            ]);

            return $os->load(['cliente', 'tecnico', 'itens']);
        });
    }

    public function listarOS(int $empresaId)
    {
        return OrdemServico::with(['cliente', 'tecnico', 'itens'])
            ->where('empresa_id', $empresaId)
            ->orderBy('id', 'desc')
            ->paginate(15);
    }

    public function atualizarStatus(int $osId, int $empresaId, string $novoStatus): OrdemServico
    {
        return DB::transaction(function () use ($osId, $empresaId, $novoStatus) {
            $os = OrdemServico::with('itens')
                ->where('id', $osId)
                ->where('empresa_id', $empresaId)
                ->firstOrFail();

            $statusAntigo = $os->status;

            if ($statusAntigo === $novoStatus) {
                return $os;
            }

            // Dentro do método atualizarStatus(), no GATILHO 1 (Concluindo a OS):
if ($novoStatus === 'CONCLUIDO' && $statusAntigo !== 'CONCLUIDO') {
    foreach ($os->itens as $itemOs) {
        $this->itemService->movimentarEstoque(
            itemId: $itemOs->item_id,
            empresaId: $empresaId,
            quantidade: (float) $itemOs->quantidade,
            operacao: 'SUBTRAIR'
        );
    }
    $os->data_conclusao = now();

    // 💰 GATILHO AUTOMÁTICO: Gera o Contas a Receber
    $this->financeiroService->gerarReceitaOrigemOS($os);
}

            // GATILHO 2: Estorno de estoque (Caso a OS estava concluída e seja alterada para outro status)
            if ($statusAntigo === 'CONCLUIDO' && $novoStatus !== 'CONCLUIDO') {
                foreach ($os->itens as $itemOs) {
                    $this->itemService->movimentarEstoque(
                        itemId: $itemOs->item_id,
                        empresaId: $empresaId,
                        quantidade: (float) $itemOs->quantidade,
                        operacao: 'SOMAR'
                    );
                }
                $os->data_conclusao = null;
            }

            $os->status = $novoStatus;
            $os->save();

            return $os->load(['cliente', 'tecnico', 'itens']);
        });
    }
}