<?php

namespace App\Http\Controllers;

use App\Modules\Orcamentos\Models\Orcamento;
use App\Modules\OrdensServico\Models\OrdemServico;
use Illuminate\Http\Request;

class ImpressaoController extends Controller
{
    // Corrigido para "orcamento" com 'o' minúsculo
    public function orcamento(Request $request, int $id)
    {
        $empresaId = $request->user()->empresa_id;

        $orcamento = Orcamento::with(['cliente', 'vendedor', 'itens.item'])
            ->where('id', $id)
            ->where('empresa_id', $empresaId)
            ->firstOrFail();

        $empresa = $request->user()->empresa;

        return view('impressao.orcamento', compact('orcamento', 'empresa'));
    }

    public function ordemServico(Request $request, int $id)
    {
        $empresaId = $request->user()->empresa_id;

        $os = OrdemServico::with(['cliente', 'tecnico', 'itens.item'])
            ->where('id', $id)
            ->where('empresa_id', $empresaId)
            ->firstOrFail();

        $empresa = $request->user()->empresa;

        return view('impressao.os', compact('os', 'empresa'));
    }
}