<?php

namespace App\Modules\RH\Services;

use App\Modules\RH\Models\Colaborador;
use App\Modules\RH\Models\Holerite;
use App\Modules\Financeiro\Models\LancamentoFinanceiro;
use Illuminate\Support\Facades\DB;

class HoleriteService
{
    public function gerarHoleriteCompetencia(int $empresaId, int $colaboradorId, string $competencia, array $proventosAdicionais = [], array $descontosAdicionais = []): Holerite
    {
        return DB::transaction(function () use ($empresaId, $colaboradorId, $competencia, $proventosAdicionais, $descontosAdicionais) {
            $colaborador = Colaborador::where('id', $colaboradorId)->where('empresa_id', $empresaId)->firstOrFail();

            $salarioBase = (float) $colaborador->salario_base;
            $totalProventos = $salarioBase;
            $totalDescontos = 0.00;

            $discriminado = [
                'proventos' => [
                    ['descricao' => 'Salário Base Mensal', 'valor' => $salarioBase]
                ],
                'descontos' => []
            ];

            foreach ($proventosAdicionais as $prov) {
                $totalProventos += (float) $prov['valor'];
                $discriminado['proventos'][] = $prov;
            }

            foreach ($descontosAdicionais as $desc) {
                $totalDescontos += (float) $desc['valor'];
                $discriminado['descontos'][] = $desc;
            }

            $valorLiquido = max(0.00, round($totalProventos - $totalDescontos, 2));

            // 1. Gera Despesa no Contas a Pagar
            $despesa = LancamentoFinanceiro::create([
                'empresa_id' => $empresaId,
                'tipo' => 'DESPESA',
                'descricao' => "Folha de Pagamento - {$colaborador->nome_completo} ({$competencia})",
                'valor' => $valorLiquido,
                'data_vencimento' => now()->addDays(5)->toDateString(),
                'status' => 'PENDENTE',
                'parcela_atual' => 1,
                'total_parcelas' => 1,
            ]);

            // 2. Persiste Holerite
            $holerite = Holerite::updateOrCreate(
                ['empresa_id' => $empresaId, 'colaborador_id' => $colaboradorId, 'mes_ano_competencia' => $competencia],
                [
                    'lancamento_financeiro_id' => $despesa->id,
                    'proventos_total' => $totalProventos,
                    'descontos_total' => $totalDescontos,
                    'valor_liquido' => $valorLiquido,
                    'itens_discriminados' => $discriminado,
                    'status' => 'GERADO',
                ]
            );

            return $holerite->load(['colaborador', 'lancamentoFinanceiro']);
        });
    }
}