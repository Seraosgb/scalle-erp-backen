<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
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

    public function index(Request $request): JsonResponse
    {
        $empresas = Empresa::where('ativo', true)->orderBy('razao_social')->get();

        return response()->json([
            'status' => 'success',
            'data' => $empresas
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'razao_social' => 'required|string|max:255',
            'nome_fantasia' => 'nullable|string|max:255',
            'cpf_cnpj' => 'required|string|max:18|unique:sis_empresas,cpf_cnpj',
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

        $empresa = Empresa::create(array_merge($validated, ['ativo' => true]));

        return response()->json([
            'status' => 'success',
            'message' => 'Nova empresa cadastrada com sucesso!',
            'data' => $empresa
        ], 201);
    }

    public function update(Request $request): JsonResponse
    {
        $empresa = $request->user()->empresa;

        $validated = $request->validate([
            'razao_social' => 'required|string|max:255',
            'nome_fantasia' => 'nullable|string|max:255',
            'cpf_cnpj' => 'required|string|max:18|unique:sis_empresas,cpf_cnpj,' . $empresa->id,
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

    public function trocarContexto(Request $request, int $empresaId): JsonResponse
    {
        $user = $request->user();

        $empresaAlvo = Empresa::where('id', $empresaId)->where('ativo', true)->first();

        if (! $empresaAlvo) {
            return response()->json([
                'status' => 'error',
                'message' => 'Empresa destino não encontrada ou inativa.'
            ], 404);
        }

        $user->update(['empresa_id' => $empresaAlvo->id]);

        return response()->json([
            'status' => 'success',
            'message' => "Contexto alterado para a empresa: {$empresaAlvo->razao_social}",
            'data' => [
                'user_id' => $user->id,
                'empresa_ativa' => [
                    'id' => $empresaAlvo->id,
                    'razao_social' => $empresaAlvo->razao_social,
                    'nome_fantasia' => $empresaAlvo->nome_fantasia,
                    'cpf_cnpj' => $empresaAlvo->cpf_cnpj,
                ]
            ]
        ]);
    }
}