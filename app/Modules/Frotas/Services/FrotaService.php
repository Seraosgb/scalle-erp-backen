<?php

namespace App\Modules\Frotas\Services;

use App\Modules\Frotas\Models\Veiculo;
use App\Modules\Frotas\Models\Abastecimento;
use App\Modules\Financeiro\Models\LancamentoFinanceiro;
use Illuminate\Support\Facades\DB;

class FrotaService
{
    public function listarVeiculos(int $empresaId)
    {
        return Veiculo::with('motoristaPadrao')
            ->where('empresa_id', $empresaId)
            ->where('ativo', true)
            ->orderBy('placa')
            ->get();
    }

    public function criarVeiculo(int $empresaId, array $data): Veiculo
    {
        return Veiculo::create([
            'empresa_id' => $empresaId,
            'motorista_padrao_id' => $data['motorista_padrao_id'] ?? null,
            'placa' => strtoupper($data['placa']),
            'modelo' => $data['modelo'],
            'marca' => $data['marca'],
            'ano_fabricacao' => $data['ano_fabricacao'] ?? null,
            'combustivel_tipo' => strtoupper($data['combustivel_tipo'] ?? 'FLEX'),
            'km_atual' => (float) ($data['km_atual'] ?? 0.00),
            'status' => 'DISPONIVEL',
            'ativo' => true,
        ]);
    }

    public function registrarAbastecimento(int $empresaId, int $motoristaId, array $data): Abastecimento
    {
        return DB::transaction(function () use ($empresaId, $motoristaId, $data) {
            $veiculo = Veiculo::where('id', $data['veiculo_id'])
                ->where('empresa_id', $empresaId)
                ->firstOrFail();

            $kmOdometro = (float) $data['km_odometro'];
            $litros = (float) $data['litros'];
            $valorLitro = (float) $data['valor_litro'];
            $valorTotal = (float) ($data['valor_total'] ?? ($litros * $valorLitro));

            // 1. Gera Despesa no Contas a Pagar
            $despesa = LancamentoFinanceiro::create([
                'empresa_id' => $empresaId,
                'tipo' => 'DESPESA',
                'descricao' => "Abastecimento Veículo {$veiculo->placa} - {$veiculo->modelo}",
                'valor' => $valorTotal,
                'data_vencimento' => now()->toDateString(),
                'status' => 'PENDENTE',
                'parcela_atual' => 1,
                'total_parcelas' => 1,
            ]);

            // 2. Grava Abastecimento
            $abastecimento = Abastecimento::create([
                'empresa_id' => $empresaId,
                'veiculo_id' => $veiculo->id,
                'motorista_id' => $motoristaId,
                'lancamento_financeiro_id' => $despesa->id,
                'data_abastecimento' => $data['data_abastecimento'] ?? now(),
                'km_odometro' => $kmOdometro,
                'litros' => $litros,
                'valor_litro' => $valorLitro,
                'valor_total' => $valorTotal,
                'posto_combustivel' => $data['posto_combustivel'] ?? null,
                'observacoes' => $data['observacoes'] ?? null,
            ]);

            // 3. Atualiza KM do veículo se maior
            if ($kmOdometro > $veiculo->km_atual) {
                $veiculo->update(['km_atual' => $kmOdometro]);
            }

            return $abastecimento->load(['veiculo', 'motorista', 'lancamentoFinanceiro']);
        });
    }

    public function listarAbastecimentos(int $empresaId, ?int $veiculoId = null)
    {
        $query = Abastecimento::with(['veiculo', 'motorista'])
            ->where('empresa_id', $empresaId);

        if ($veiculoId) {
            $query->where('veiculo_id', $veiculoId);
        }

        return $query->orderBy('data_abastecimento', 'desc')->paginate(15);
    }
}