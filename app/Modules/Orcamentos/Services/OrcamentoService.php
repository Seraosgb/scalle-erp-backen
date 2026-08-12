<?php

namespace App\Modules\Orcamentos\Services;

use App\Modules\Orcamentos\Models\Orcamento;
use App\Modules\Orcamentos\Models\OrcamentoItem;
use App\Modules\Produtos\Models\Item;
use App\Modules\Vendas\Services\VendaService;
use App\Modules\OrdensServico\Services\OrdemServicoService;
use App\Modules\OrdensServico\DTOs\OrdemServicoDTO;
use Illuminate\Support\Facades\DB;

class OrcamentoService
{
    public function __construct(
        private VendaService $vendaService,
        private OrdemServicoService $osService
    ) {}

    public function criarOrcamento(int $empresaId, int $vendedorId, array $data): Orcamento
    {
        return DB::transaction(function () use ($empresaId, $vendedorId, $data) {
            $numero = OrcamentoNumeroService::gerarProximoNumero($empresaId);

            $orcamento = Orcamento::create([
                'empresa_id' => $empresaId,
                'cliente_id' => $data['cliente_id'],
                'vendedor_id' => $vendedorId,
                'numero_orcamento' => $numero,
                'status' => 'EM_ABERTO',
                'data_emissao' => now(),
                'data_validade' => $data['data_validade'] ?? now()->addDays(7)->toDateString(),
                'valor_desconto' => (float) ($data['valor_desconto'] ?? 0),
                'observacoes' => $data['observacoes'] ?? null,
                'valor_subtotal' => 0.00,
                'valor_total' => 0.00,
            ]);

            $subtotal = 0.00;

            foreach ($data['itens'] as $itemRequest) {
                $itemCadastrado = Item::where('id', $itemRequest['item_id'])
                    ->where('empresa_id', $empresaId)
                    ->firstOrFail();

                $qtd = (float) $itemRequest['quantidade'];
                $unitario = (float) ($itemRequest['valor_unitario'] ?? $itemCadastrado->preco_venda);
                $sub = $qtd * $unitario;
                $subtotal += $sub;

                OrcamentoItem::create([
                    'orcamento_id' => $orcamento->id,
                    'item_id' => $itemCadastrado->id,
                    'descricao_item' => $itemCadastrado->nome,
                    'quantidade' => $qtd,
                    'valor_unitario' => $unitario,
                    'valor_subtotal' => $sub,
                ]);
            }

            $orcamento->update([
                'valor_subtotal' => $subtotal,
                'valor_total' => max(0, $subtotal - $orcamento->valor_desconto),
            ]);

            return $orcamento->load(['cliente', 'vendedor', 'itens.item']);
        });
    }

    public function listarOrcamentos(int $empresaId)
    {
        return Orcamento::with(['cliente', 'vendedor', 'itens.item'])
            ->where('empresa_id', $empresaId)
            ->orderBy('id', 'desc')
            ->paginate(15);
    }

    /**
     * Converte o Orçamento Aprovado em Venda Direta ou Ordem de Serviço
     */
    public function converterOrcamento(int $orcamentoId, int $empresaId, string $destino): array
    {
        return DB::transaction(function () use ($orcamentoId, $empresaId, $destino) {
            $orcamento = Orcamento::with('itens')
                ->where('id', $orcamentoId)
                ->where('empresa_id', $empresaId)
                ->firstOrFail();

            if ($orcamento->status === 'CONVERTIDO') {
                throw new \Exception('Este orçamento já foi convertido anteriormente.');
            }

            $itensFormatados = $orcamento->itens->map(function ($item) {
                return [
                    'item_id' => $item->item_id,
                    'quantidade' => (float) $item->quantidade,
                    'valor_unitario' => (float) $item->valor_unitario,
                ];
            })->toArray();

            $resultado = [];

            if ($destino === 'VENDA') {
                $venda = $this->vendaService->registrarVenda($empresaId, $orcamento->vendedor_id, [
                    'cliente_id' => $orcamento->cliente_id,
                    'valor_desconto' => $orcamento->valor_desconto,
                    'observacoes' => "Gerado a partir do Orçamento {$orcamento->numero_orcamento}",
                    'itens' => $itensFormatados,
                ]);
                $resultado = ['tipo' => 'VENDA', 'objeto' => $venda];
            } else if ($destino === 'OS') {
                $dto = OrdemServicoDTO::fromRequest([
                    'cliente_id' => $orcamento->cliente_id,
                    'tecnico_id' => $orcamento->vendedor_id,
                    'valor_desconto' => $orcamento->valor_desconto,
                    'observacoes_internas' => "Gerado a partir do Orçamento {$orcamento->numero_orcamento}",
                    'itens' => $itensFormatados,
                ], $empresaId);

                $os = $this->osService->criarOS($dto);
                $resultado = ['tipo' => 'OS', 'objeto' => $os];
            }

            $orcamento->update(['status' => 'CONVERTIDO']);

            return $resultado;
        });
    }
}