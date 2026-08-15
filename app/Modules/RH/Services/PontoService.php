<?php

namespace App\Modules\RH\Services;

use App\Modules\RH\Models\Colaborador;
use App\Modules\RH\Models\Ponto;
use App\Modules\RH\Models\BancoHoras;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PontoService
{
    public function baterPonto(int $empresaId, int $colaboradorId, ?string $lat, ?string $long, ?string $ip): Ponto
    {
        return DB::transaction(function () use ($empresaId, $colaboradorId, $lat, $long, $ip) {
            $colaborador = Colaborador::with('escala')->where('id', $colaboradorId)->where('empresa_id', $empresaId)->firstOrFail();
            $hoje = Carbon::today()->toDateString();
            $agora = Carbon::now();

            $ponto = Ponto::firstOrCreate(
                ['empresa_id' => $empresaId, 'colaborador_id' => $colaboradorId, 'data_referencia' => $hoje],
                [
                    'entrada_1' => $agora,
                    'latitude' => $lat,
                    'longitude' => $long,
                    'ip_registro' => $ip,
                ]
            );

            // Sequência de batidas: Entrada 1 -> Saída 1 -> Entrada 2 -> Saída 2
            if ($ponto->wasRecentlyCreated) {
                return $ponto;
            }

            if (!$ponto->saida_1) {
                $ponto->saida_1 = $agora;
            } elseif (!$ponto->entrada_2) {
                $ponto->entrada_2 = $agora;
            } elseif (!$ponto->saida_2) {
                $ponto->saida_2 = $agora;
            }

            // Calcula total trabalhado
            $totalMinutos = 0;
            if ($ponto->entrada_1 && $ponto->saida_1) {
                $totalMinutos += Carbon::parse($ponto->entrada_1)->diffInMinutes(Carbon::parse($ponto->saida_1));
            }
            if ($ponto->entrada_2 && $ponto->saida_2) {
                $totalMinutos += Carbon::parse($ponto->entrada_2)->diffInMinutes(Carbon::parse($ponto->saida_2));
            }

            $horasTrabalhadas = round($totalMinutos / 60, 2);
            $ponto->total_horas_trabalhadas = $horasTrabalhadas;

            // Calcula saldo do dia com base na escala
            $horasEsperadas = (float) ($colaborador->escala->horas_diarias_padrao ?? 8.80);
            $saldoDia = round($horasTrabalhadas - $horasEsperadas, 2);
            $ponto->saldo_horas_dia = $saldoDia;
            $ponto->save();

            // Lança no Banco de Horas se houver saldo e a política for BANCO_HORAS
            if ($saldoDia != 0 && ($colaborador->escala?->politica_extra ?? 'BANCO_HORAS') === 'BANCO_HORAS') {
                BancoHoras::updateOrCreate(
                    ['empresa_id' => $empresaId, 'colaborador_id' => $colaboradorId, 'ponto_id' => $ponto->id],
                    [
                        'data_lancamento' => $hoje,
                        'tipo' => $saldoDia > 0 ? 'CREDITO' : 'DEBITO',
                        'horas' => abs($saldoDia),
                        'motivo' => "Apuração automática de ponto em {$hoje}",
                    ]
                );
            }

            return $ponto;
        });
    }
}