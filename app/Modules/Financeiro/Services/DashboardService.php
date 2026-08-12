<?php

namespace App\Modules\Financeiro\Services;

use App\Modules\OrdensServico\Models\OrdemServico;
use App\Modules\Vendas\Models\Venda;
use App\Modules\Orcamentos\Models\Orcamento;
use App\Modules\Financeiro\Models\LancamentoFinanceiro;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    public function obterResumo(int $empresaId): array
    {
        $hoje = now()->toDateString();
        $inicioMes = now()->startOfMonth()->toDateString();
        $fimMes = now()->endOfMonth()->toDateString();

        // 🛠️ 1. OPERACIONAL (OS & Orçamentos)
        $osAbertas = OrdemServico::where('empresa_id', $empresaId)
            ->whereIn('status', ['ABERTO', 'EM_ANDAMENTO', 'AGUARDANDO_PECA'])
            ->count();

        $osConcluidasMes = OrdemServico::where('empresa_id', $empresaId)
            ->where('status', 'CONCLUIDO')
            ->whereBetween('data_conclusao', [$inicioMes . ' 00:00:00', $fimMes . ' 23:59:59'])
            ->count();

        $orcamentosAbertos = Orcamento::where('empresa_id', $empresaId)
            ->where('status', 'EM_ABERTO')
            ->count();

        // 🏬 2. COMERCIAL (Vendas Diretas)
        $vendasHoje = Venda::where('empresa_id', $empresaId)
            ->where('status', 'CONCLUIDO')
            ->whereDate('data_venda', $hoje)
            ->sum('valor_total');

        $vendasMes = Venda::where('empresa_id', $empresaId)
            ->where('status', 'CONCLUIDO')
            ->whereBetween('data_venda', [$inicioMes . ' 00:00:00', $fimMes . ' 23:59:59'])
            ->sum('valor_total');

        // 💰 3. FINANCEIRO & FLUXO
        $receberVencido = LancamentoFinanceiro::where('empresa_id', $empresaId)
            ->where('tipo', 'RECEITA')
            ->where('status', 'PENDENTE')
            ->where('data_vencimento', '<', $hoje)
            ->sum('valor');

        $receberMes = LancamentoFinanceiro::where('empresa_id', $empresaId)
            ->where('tipo', 'RECEITA')
            ->where('status', 'PENDENTE')
            ->whereBetween('data_vencimento', [$inicioMes, $fimMes])
            ->sum('valor');

        $pagarVencido = LancamentoFinanceiro::where('empresa_id', $empresaId)
            ->where('tipo', 'DESPESA')
            ->where('status', 'PENDENTE')
            ->where('data_vencimento', '<', $hoje)
            ->sum('valor');

        $pagarMes = LancamentoFinanceiro::where('empresa_id', $empresaId)
            ->where('tipo', 'DESPESA')
            ->where('status', 'PENDENTE')
            ->whereBetween('data_vencimento', [$inicioMes, $fimMes])
            ->sum('valor');

        $receitasRealizadasMes = LancamentoFinanceiro::where('empresa_id', $empresaId)
            ->where('tipo', 'RECEITA')
            ->where('status', 'PAGO')
            ->whereBetween('data_pagamento', [$inicioMes, $fimMes])
            ->sum('valor');

        $despesasRealizadasMes = LancamentoFinanceiro::where('empresa_id', $empresaId)
            ->where('tipo', 'DESPESA')
            ->where('status', 'PAGO')
            ->whereBetween('data_pagamento', [$inicioMes, $fimMes])
            ->sum('valor');

        return [
            'operacional' => [
                'os_em_andamento' => $osAbertas,
                'os_concluidas_mes' => $osConcluidasMes,
                'orcamentos_pendentes' => $orcamentosAbertos,
            ],
            'comercial' => [
                'vendas_hoje' => number_format($vendasHoje, 2, '.', ''),
                'faturamento_vendas_mes' => number_format($vendasMes, 2, '.', ''),
            ],
            'financeiro' => [
                'contas_receber' => [
                    'vencido' => number_format($receberVencido, 2, '.', ''),
                    'a_vencer_mes' => number_format($receberMes, 2, '.', ''),
                ],
                'contas_pagar' => [
                    'vencido' => number_format($pagarVencido, 2, '.', ''),
                    'a_vencer_mes' => number_format($pagarMes, 2, '.', ''),
                ],
                'saldo_realizado_mes' => number_format($receitasRealizadasMes - $despesasRealizadasMes, 2, '.', ''),
            ],
        ];
    }
}