<?php

namespace App\Modules\Ativos\Services;

use App\Modules\Ativos\Models\Ativo;
use App\Modules\Ativos\Models\Cautela;
use Illuminate\Support\Facades\DB;

class AtivoService
{
    public function listarAtivos(int $empresaId, array $filtros = [])
    {
        $query = Ativo::with(['custodianteAtual', 'item'])
            ->where('empresa_id', $empresaId)
            ->where('ativo', true);

        if (!empty($filtros['categoria'])) {
            $query->where('categoria', strtoupper($filtros['categoria']));
        }

        if (!empty($filtros['status'])) {
            $query->where('status', strtoupper($filtros['status']));
        }

        return $query->orderBy('codigo_patrimonio')->get()->map(function ($ativo) {
            $ativo->valor_residual = $ativo->valor_residual;
            return $ativo;
        });
    }

    public function criarAtivo(int $empresaId, array $data): Ativo
    {
        $codigo = PatrimonioNumeroService::gerarProximoNumero($empresaId);

        return Ativo::create([
            'empresa_id' => $empresaId,
            'item_id' => $data['item_id'] ?? null,
            'codigo_patrimonio' => $codigo,
            'nome' => $data['nome'],
            'categoria' => strtoupper($data['categoria'] ?? 'FERRAMENTA'),
            'numero_serie' => $data['numero_serie'] ?? null,
            'data_aquisicao' => $data['data_aquisicao'] ?? now()->toDateString(),
            'valor_aquisicao' => (float) ($data['valor_aquisicao'] ?? 0.00),
            'taxa_depreciacao_anual' => (float) ($data['taxa_depreciacao_anual'] ?? 0.00),
            'status' => 'DISPONIVEL',
            'observacoes' => $data['observacoes'] ?? null,
            'ativo' => true,
        ]);
    }

    public function emitirTermoCautela(int $empresaId, int $responsavelEntregaId, array $data, ?string $ip): Cautela
    {
        return DB::transaction(function () use ($empresaId, $responsavelEntregaId, $data, $ip) {
            $ativo = Ativo::where('id', $data['ativo_id'])
                ->where('empresa_id', $empresaId)
                ->firstOrFail();

            if ($ativo->status === 'EM_CUSTODIA') {
                throw new \Exception("Este ativo já se encontra em custódia com outro colaborador.");
            }

            $cautela = Cautela::create([
                'empresa_id' => $empresaId,
                'ativo_id' => $ativo->id,
                'responsavel_entrega_id' => $responsavelEntregaId,
                'custodiante_id' => $data['custodiante_id'],
                'data_retirada' => now(),
                'data_devolucao_prevista' => $data['data_devolucao_prevista'] ?? now()->addDays(15),
                'status' => 'EM_CUSTODIA',
                'ip_assinatura' => $ip,
                'motivo_uso' => $data['motivo_uso'] ?? null,
            ]);

            $ativo->update([
                'status' => 'EM_CUSTODIA',
                'custodiante_atual_id' => $data['custodiante_id']
            ]);

            return $cautela->load(['ativo', 'custodiante', 'responsavelEntrega']);
        });
    }

    public function devolverCautela(int $cautelaId, int $empresaId, array $data): Cautela
    {
        return DB::transaction(function () use ($cautelaId, $empresaId, $data) {
            $cautela = Cautela::with('ativo')
                ->where('id', $cautelaId)
                ->where('empresa_id', $empresaId)
                ->firstOrFail();

            if ($cautela->status !== 'EM_CUSTODIA') {
                throw new \Exception("Esta cautela já foi finalizada anteriormente.");
            }

            $statusDevolucao = strtoupper($data['status_devolucao'] ?? 'DEVOLVIDO'); // DEVOLVIDO ou AVARIADO

            $cautela->update([
                'data_devolucao_real' => now(),
                'status' => $statusDevolucao,
                'observacoes_devolucao' => $data['observacoes_devolucao'] ?? null,
            ]);

            $cautela->ativo->update([
                'status' => $statusDevolucao === 'AVARIADO' ? 'AVARIADO' : 'DISPONIVEL',
                'custodiante_atual_id' => null
            ]);

            return $cautela->load(['ativo', 'custodiante']);
        });
    }
}