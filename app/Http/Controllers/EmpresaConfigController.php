<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class EmpresaConfigController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $empresa = $request->user()->empresa;

        return response()->json([
            'status' => 'success',
            'data' => $empresa
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $empresa = $request->user()->empresa;

        $validated = $request->validate([
            'razao_social' => 'required|string|max:255',
            'nome_fantasia' => 'nullable|string|max:255',
            'cpf_cnpj' => 'required|string|max:18',
            'inscricao_estadual' => 'nullable|string|max:20',
            'inscricao_municipal' => 'nullable|string|max:20',
            'crt' => 'required|integer|in:1,2,3',
            'cep' => 'nullable|string|max:10',
            'logradouro' => 'nullable|string|max:255',
            'numero' => 'nullable|string|max:20',
            'complemento' => 'nullable|string|max:100',
            'bairro' => 'nullable|string|max:100',
            'cidade' => 'nullable|string|max:100',
            'uf' => 'nullable|string|size:2',
            'codigo_ibge' => 'nullable|string|size:7',
        ]);

        $empresa->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Configurações fiscais da empresa atualizadas com sucesso!',
            'data' => $empresa
        ]);
    }
}