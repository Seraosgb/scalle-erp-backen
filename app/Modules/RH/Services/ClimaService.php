<?php

namespace App\Modules\RH\Services;

use App\Modules\RH\Models\ClimaPesquisa;
use App\Modules\RH\Models\ClimaResposta;

class ClimaService
{
    public function calcularIndicadoresEnps(int $pesquisaId, int $empresaId): array
    {
        $pesquisa = ClimaPesquisa::where('id', $pesquisaId)->where('empresa_id', $empresaId)->firstOrFail();
        $respostas = ClimaResposta::where('pesquisa_id', $pesquisaId)->get();

        $totalRespostas = $respostas->count();
        if ($totalRespostas === 0) {
            return [
                'pesquisa' => $pesquisa->titulo,
                'total_respostas' => 0,
                'enps_score' => 0,
                'promotores' => 0,
                'neutros' => 0,
                'detratores' => 0,
            ];
        }

        $promotores = $respostas->where('nota_enps', '>=', 9)->count();
        $neutros = $respostas->whereBetween('nota_enps', [7, 8])->count();
        $detratores = $respostas->where('nota_enps', '<=', 6)->count();

        $enps = round((($promotores - $detratores) / $totalRespostas) * 100, 2);

        return [
            'pesquisa' => $pesquisa->titulo,
            'total_respostas' => $totalRespostas,
            'enps_score' => $enps,
            'promotores' => $promotores,
            'neutros' => $neutros,
            'detratores' => $detratores,
        ];
    }
}