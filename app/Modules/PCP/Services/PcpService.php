<?php

namespace App\Modules\PCP\Services;

use App\Modules\PCP\Models\FichaTecnica;
use App\Modules\PCP\Models\OrdemProducao;
use App\Modules\PCP\Models\ApontamentoPerda;
use App\Modules\Produtos\Models\Item;
use App\Modules\Produtos\Services\ItemService;
use Illuminate\Support\Facades\DB;

class PcpService
{
    public function __construct(private ItemService $itemService) {}

    /**
     * Salva ou atualiza a Ficha Técnica (BOM) do Produto
     */
    public function salvarFichaTecnica(int $empresaId, int $produtoPaiId, array $componentes): array
    {
        return DB::transaction(function () use ($empresaId, $produtoPaiId, $componentes) {
            FichaTecnica::where('empresa_id', $empresaId)
                ->where('produto_pai_id', $produtoPaiId)
                ->delete();

            $fichas = [];
            foreach ($componentes as $comp) {
                $fichas[] = FichaTecnica::create([
                    'empresa_id' => $empresaId,
                    'produto_pai_id' => $produtoPaiId,
                    'insumo_id' => $comp['insumo_id'] ?? null,
                    'tipo_componente' => strtoupper($comp['tipo_componente'] ?? 'INSUMO'),
                    'descricao_custo' => $comp['descricao_custo'] ?? null,
                    'quantidade_necessaria' => (float) $comp['quantidade_necessaria'],
                    'custo_estimado' => (float) ($comp['custo_estimado'] ?? 0),
                ]);
            }

            return $fichas;
        });
    }

    /**
     * Cria a Ordem de Produção (Status: PLANEJADA)
     */
    public function criarOP(int $empresaId, int $responsavelId, array $data): OrdemProducao
    {
        $numeroOp = OpNumeroService::gerarProximoNumero($empresaId);

        return OrdemProducao::create([
            'empresa_id' => $empresaId,
            'produto_acabado_id' => $data['produto_acabado_id'],
            'responsavel_id' => $responsavelId,
            'numero_op' => $numeroOp,
            'status' => 'PLANEJADA',
            'quantidade_planejada' => (float) $data['quantidade_planejada'],
            'data_inicio_prevista' => $data['data_inicio_prevista'] ?? now(),
            'data_conclusao_prevista' => $data['data_conclusao_prevista'] ?? now()->addDays(2),
            'observacoes' => $data['observacoes'] ?? null,
        ]);
    }

    /**
     * Altera o status da OP e executa baixa/entrada atômica de estoque na conclusão
     */
    public function atualizarStatusOP(int $opId, int $empresaId, string $novoStatus, ?float $qtdProduzida = null): OrdemProducao
    {
        return DB::transaction(function () use ($opId, $empresaId, $novoStatus, $qtdProduzida) {
            $op = OrdemProducao::where('id', $opId)
                ->where('empresa_id', $empresaId)
                ->firstOrFail();

            if ($op->status === 'CONCLUIDA') {
                throw new \Exception('Esta Ordem de Produção já foi finalizada anteriormente.');
            }

            if ($novoStatus === 'EM_PRODUCAO' && $op->status === 'PLANEJADA') {
                $op->data_inicio_real = now();
            }

            if ($novoStatus === 'CONCLUIDA') {
                $quantidadeFinal = $qtdProduzida ?? $op->quantidade_planejada;
                $fichas = FichaTecnica::with('insumo')
                    ->where('empresa_id', $empresaId)
                    ->where('produto_pai_id', $op->produto_acabado_id)
                    ->get();

                if ($fichas->isEmpty()) {
                    throw new \Exception('Não é possível concluir a OP sem uma Ficha Técnica (BOM) cadastrada para o produto.');
                }

                $custoInsumos = 0.00;
                $custoAdicional = 0.00;

                // 📦 1. Baixa Atômica dos Insumos Consumidos
                foreach ($fichas as $itemBOM) {
                    if ($itemBOM->tipo_componente === 'INSUMO' && $itemBOM->insumo_id) {
                        $qtdConsumida = $itemBOM->quantidade_necessaria * $quantidadeFinal;
                        $precoCustoUnit = (float) $itemBOM->insumo->preco_custo;
                        $custoInsumos += ($qtdConsumida * $precoCustoUnit);

                        $this->itemService->movimentarEstoque(
                            itemId: $itemBOM->insumo_id,
                            empresaId: $empresaId,
                            quantidade: $qtdConsumida,
                            operacao: 'SUBTRAIR'
                        );
                    } else {
                        // Custo de mão de obra / máquinas (CIF)
                        $custoAdicional += ($itemBOM->custo_estimado * $quantidadeFinal);
                    }
                }

                // 📦 2. Entrada Atômica do Produto Acabado no Estoque
                $this->itemService->movimentarEstoque(
                    itemId: $op->produto_acabado_id,
                    empresaId: $empresaId,
                    quantidade: $quantidadeFinal,
                    operacao: 'SOMAR'
                );

                $custoTotal = $custoInsumos + $custoAdicional;
                $custoUnitario = $quantidadeFinal > 0 ? ($custoTotal / $quantidadeFinal) : 0;

                // Atualiza o Preço de Custo apurado do Produto Acabado
                Item::where('id', $op->produto_acabado_id)->update(['preco_custo' => $custoUnitario]);

                $op->quantidade_produzida = $quantidadeFinal;
                $op->custo_total_insumos = $custoInsumos;
                $op->custo_total_adicional = $custoAdicional;
                $op->custo_total_producao = $custoTotal;
                $op->custo_unitario_final = $custoUnitario;
                $op->data_conclusao_real = now();
            }

            $op->status = $novoStatus;
            $op->save();

            return $op->load(['produtoAcabado', 'responsavel', 'perdas']);
        });
    }

    /**
     * Registra o apontamento de refugo/perda durante a produção
     */
    public function registrarPerda(int $empresaId, int $opId, array $data): ApontamentoPerda
    {
        return DB::transaction(function () use ($empresaId, $opId, $data) {
            $insumo = Item::where('id', $data['insumo_id'])
                ->where('empresa_id', $empresaId)
                ->firstOrFail();

            $qtdPerdida = (float) $data['quantidade_perdida'];
            $custoPerda = $qtdPerdida * (float) $insumo->preco_custo;

            // Baixa do saldo que foi perdido/avariado
            $this->itemService->movimentarEstoque(
                itemId: $insumo->id,
                empresaId: $empresaId,
                quantidade: $qtdPerdida,
                operacao: 'SUBTRAIR'
            );

            return ApontamentoPerda::create([
                'empresa_id' => $empresaId,
                'ordem_producao_id' => $opId,
                'insumo_id' => $insumo->id,
                'motivo_perda_id' => $data['motivo_perda_id'] ?? null,
                'quantidade_perdida' => $qtdPerdida,
                'custo_perda' => $custoPerda,
                'observacoes' => $data['observacoes'] ?? null,
            ]);
        });
    }
}