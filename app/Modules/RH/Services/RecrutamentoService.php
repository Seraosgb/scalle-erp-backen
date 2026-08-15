<?php

namespace App\Modules\RH\Services;

use App\Modules\RH\Models\Candidato;
use App\Modules\RH\Models\Colaborador;
use Illuminate\Support\Facades\DB;

class RecrutamentoService
{
    public function atualizarEtapaKanban(int $candidatoId, int $empresaId, string $novaEtapa, ?string $feedback = null): Candidato
    {
        return DB::transaction(function () use ($candidatoId, $empresaId, $novaEtapa, $feedback) {
            $candidato = Candidato::with('vaga')
                ->where('id', $candidatoId)
                ->where('empresa_id', $empresaId)
                ->firstOrFail();

            $candidato->etapa_kanban = strtoupper($novaEtapa);
            if ($feedback) {
                $candidato->feedback_entrevista = $feedback;
            }
            $candidato->save();

            // Auto-Admissão: Cria rascunho funcional se contratado
            if ($candidato->etapa_kanban === 'CONTRATADO') {
                $matricula = "MAT-" . date('Y') . "-" . str_pad((string) (Colaborador::where('empresa_id', $empresaId)->count() + 1), 4, '0', STR_PAD_LEFT);

                Colaborador::firstOrCreate(
                    ['empresa_id' => $empresaId, 'cpf' => $candidato->cpf ?? '000.000.000-00'],
                    [
                        'matricula' => $matricula,
                        'nome_completo' => $candidato->nome_completo,
                        'cargo' => $candidato->vaga->titulo,
                        'departamento' => $candidato->vaga->departamento,
                        'data_admissao' => now()->toDateString(),
                        'salario_base' => (float) ($candidato->vaga->salario_proposto ?? 0.00),
                        'tipo_contrato' => $candidato->vaga->regime_contratacao,
                        'status' => 'ATIVO',
                    ]
                );
            }

            return $candidato;
        });
    }
}