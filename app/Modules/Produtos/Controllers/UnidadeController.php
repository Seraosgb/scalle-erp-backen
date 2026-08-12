<?php

namespace App\Modules\Produtos\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Produtos\Services\UnidadeService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class UnidadeController extends Controller
{
    public function __construct(private UnidadeService $unidadeService) {}

    public function index(Request $request): JsonResponse
    {
        $data = $this->unidadeService->listar($request->user()->empresa_id);
        return response()->json(['status' => 'success', 'data' => $data]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'sigla' => 'required|string|max:10',
            'nome' => 'required|string|max:50',
        ]);
        $unidade = $this->unidadeService->criar($request->user()->empresa_id, $request->all());
        return response()->json(['status' => 'success', 'data' => $unidade], 201);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $this->unidadeService->inativar($id, $request->user()->empresa_id);
        return response()->json(['status' => 'success', 'message' => 'Unidade inativada com sucesso.']);
    }
}