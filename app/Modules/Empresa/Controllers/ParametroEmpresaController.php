<?php

namespace App\Modules\Empresa\Controllers;

use App\Http\Controllers\Controller;
use App\Models\EmpresaParametro;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ParametroEmpresaController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $parametros = EmpresaParametro::where('empresa_id', $request->user()->empresa_id)
            ->pluck('valor', 'chave');

        return response()->json([
            'status' => 'success',
            'data' => $parametros
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'parametros' => 'required|array',
            'parametros.*' => 'nullable|string',
        ]);

        $empresaId = $request->user()->empresa_id;

        foreach ($validated['parametros'] as $chave => $valor) {
            EmpresaParametro::updateOrCreate(
                ['empresa_id' => $empresaId, 'chave' => $chave],
                ['valor' => $valor]
            );
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Parâmetros operacionais salvos com sucesso!'
        ]);
    }
}