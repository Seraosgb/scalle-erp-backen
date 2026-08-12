<?php

namespace App\Modules\Financeiro\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Financeiro\Services\CategoriaFinanceiraService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CategoriaFinanceiraController extends Controller
{
    public function __construct(private CategoriaFinanceiraService $service) {}

    public function index(Request $request): JsonResponse
    {
        $data = $this->service->listar($request->user()->empresa_id, $request->query('tipo'));
        return response()->json(['status' => 'success', 'data' => $data]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'nome' => 'required|string|max:255',
            'tipo' => 'required|in:RECEITA,DESPESA,receita,despesa',
        ]);
        $categoria = $this->service->criar($request->user()->empresa_id, $request->all());
        return response()->json(['status' => 'success', 'data' => $categoria], 201);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $this->service->inativar($id, $request->user()->empresa_id);
        return response()->json(['status' => 'success', 'message' => 'Categoria financeira inativada com sucesso.']);
    }
}