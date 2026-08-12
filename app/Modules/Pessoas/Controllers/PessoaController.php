<?php

namespace App\Modules\Pessoas\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Pessoas\Services\PessoaService;
use App\Modules\Pessoas\DTOs\PessoaDTO;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PessoaController extends Controller
{
    public function __construct(private PessoaService $pessoaService) {}

    public function index(Request $request): JsonResponse
    {
        $empresaId = $request->user()->empresa_id;
        $pessoas = $this->pessoaService->listarPessoas($empresaId, $request->only(['busca', 'tipo']));

        return response()->json([
            'status' => 'success',
            'data' => $pessoas
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'nome_razao' => 'required|string|max:255',
            'cpf_cnpj' => 'required|string|unique:pes_pessoas,cpf_cnpj',
            'email' => 'nullable|email',
        ]);

        $empresaId = $request->user()->empresa_id;
        $dto = PessoaDTO::fromRequest($request->all(), $empresaId);
        $pessoa = $this->pessoaService->criarPessoa($dto);

        return response()->json([
            'status' => 'success',
            'data' => $pessoa
        ], 201);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $empresaId = $request->user()->empresa_id;
        $pessoa = $this->pessoaService->buscarPorId($id, $empresaId);

        if (! $pessoa) {
            return response()->json(['status' => 'error', 'message' => 'Pessoa não encontrada.'], 404);
        }

        return response()->json(['status' => 'success', 'data' => $pessoa]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $empresaId = $request->user()->empresa_id;
        $pessoa = $this->pessoaService->buscarPorId($id, $empresaId);

        if (! $pessoa) {
            return response()->json(['status' => 'error', 'message' => 'Pessoa não encontrada.'], 404);
        }

        $request->validate([
            'nome_razao' => 'required|string|max:255',
            'cpf_cnpj' => 'required|string|unique:pes_pessoas,cpf_cnpj,' . $id,
            'email' => 'nullable|email',
        ]);

        $dto = PessoaDTO::fromRequest($request->all(), $empresaId);
        $pessoaAtualizada = $this->pessoaService->atualizarPessoa($pessoa, $dto);

        return response()->json(['status' => 'success', 'data' => $pessoaAtualizada]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $empresaId = $request->user()->empresa_id;
        $pessoa = $this->pessoaService->buscarPorId($id, $empresaId);

        if (! $pessoa) {
            return response()->json(['status' => 'error', 'message' => 'Pessoa não encontrada.'], 404);
        }

        $this->pessoaService->inativarPessoa($pessoa);

        return response()->json([
            'status' => 'success',
            'message' => 'Pessoa inativada com sucesso.'
        ]);
    }
}