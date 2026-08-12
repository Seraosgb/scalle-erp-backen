<?php

namespace App\Modules\Financeiro\Services;

use App\Modules\Financeiro\Models\LancamentoFinanceiro;
use App\Modules\OrdensServico\Models\OrdemServico;
use Illuminate\Support\Facades\DB;
use App\Modules\Compras\Models\Compra;

class LancamentoFinanceiroService
{
    /**
     * Gera automaticamente o Contas a Receber quando uma OS é Concluída.
     */
    public function gerarReceitaOrigemOS(OrdemServico $os): LancamentoFinanceiro
    {
        return LancamentoFinanceiro::create([
            'empresa_id' => $os->empresa_id,
            'pessoa_id' => $os->cliente_id,
            'ordem_servico_id' => $os->id,
            'tipo' => 'RECEITA',
            'descricao' => "Faturamento relativo à {$os->numero_os}",
            'valor' => $os->valor_total,
            'data_vencimento' => now()->addDays(7), // Vencimento padrão em 7 dias
            'status' => 'PENDENTE',
            'parcela_atual' => 1,
            'total_parcelas' => 1,
        ]);
    }

    public function listarLancamentos(int $empresaId, array $filtros = [])
    {
        $query = LancamentoFinanceiro::with(['pessoa', 'categoria', 'ordemServico'])
            ->where('empresa_id', $empresaId);

        if (!empty($filtros['tipo'])) {
            $query->where('tipo', strtoupper($filtros['tipo']));
        }

        if (!empty($filtros['status'])) {
            $query->where('status', strtoupper($filtros['status']));
        }

        return $query->orderBy('data_vencimento', 'asc')->paginate(15);
    }
    public function baixarLancamento(int $id, int $empresaId, string $formaPagamento, ?string $dataPagamento = null): LancamentoFinanceiro
{
    $lancamento = LancamentoFinanceiro::where('id', $id)
        ->where('empresa_id', $empresaId)
        ->firstOrFail();

    $lancamento->update([
        'status' => 'PAGO',
        'forma_pagamento' => strtoupper($formaPagamento),
        'data_pagamento' => $dataPagamento ?? now()->format('Y-m-d'),
    ]);

    return $lancamento->load(['pessoa', 'categoria', 'ordemServico']);
}
/**
 * Gera automaticamente o Contas a Pagar quando uma Compra/Nota é Recebida.
 */
public function gerarDespesaOrigemCompra(Compra $compra): LancamentoFinanceiro
{
    return LancamentoFinanceiro::create([
        'empresa_id' => $compra->empresa_id,
        'pessoa_id' => $compra->fornecedor_id,
        'tipo' => 'DESPESA',
        'descricao' => "Pagamento de Fornecedor - Compra/Nota nº " . ($compra->numero_nota ?? $compra->id),
        'valor' => $compra->valor_total,
        'data_vencimento' => now()->addDays(15), // Vencimento padrão em 15 dias
        'status' => 'PENDENTE',
        'parcela_atual' => 1,
        'total_parcelas' => 1,
    ]);
}
public function obterResumoDRE(int $empresaId, ?string $dataInicio = null, ?string $dataFim = null): array
{
    $query = LancamentoFinanceiro::where('empresa_id', $empresaId);

    if ($dataInicio && $dataFim) {
        $query->whereBetween('data_vencimento', [$dataInicio, $dataFim]);
    } else {
        // Padrão: Mês Atual
        $query->whereMonth('data_vencimento', now()->month)
              ->whereYear('data_vencimento', now()->year);
    }

    $lancamentos = $query->get();

    $receitasPagas = $lancamentos->where('tipo', 'RECEITA')->where('status', 'PAGO')->sum('valor');
    $receitasPendentes = $lancamentos->where('tipo', 'RECEITA')->where('status', 'PENDENTE')->sum('valor');

    $despesasPagas = $lancamentos->where('tipo', 'DESPESA')->where('status', 'PAGO')->sum('valor');
    $despesasPendentes = $lancamentos->where('tipo', 'DESPESA')->where('status', 'PENDENTE')->sum('valor');

    $totalReceitas = $receitasPagas + $receitasPendentes;
    $totalDespesas = $despesasPagas + $despesasPendentes;
    $lucroLiquido = $receitasPagas - $despesasPagas;

    return [
        'periodo' => [
            'inicio' => $dataInicio ?? now()->startOfMonth()->toDateString(),
            'fim' => $dataFim ?? now()->endOfMonth()->toDateString(),
        ],
        'receitas' => [
            'pago' => number_format($receitasPagas, 2, '.', ''),
            'pendente' => number_format($receitasPendentes, 2, '.', ''),
            'total' => number_format($totalReceitas, 2, '.', ''),
        ],
        'despesas' => [
            'pago' => number_format($despesasPagas, 2, '.', ''),
            'pendente' => number_format($despesasPendentes, 2, '.', ''),
            'total' => number_format($totalDespesas, 2, '.', ''),
        ],
        'resultado' => [
            'lucro_liquido_realizado' => number_format($lucroLiquido, 2, '.', ''),
            'situacao' => $lucroLiquido >= 0 ? 'LUCRO' : 'PREJUIZO',
        ]
    ];
}
}