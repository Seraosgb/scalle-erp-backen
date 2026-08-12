<?php

namespace App\Modules\Produtos\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Produtos\Services\CategoriaService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CategoriaController extends Controller
{
    public function __construct(private CategoriaService $categoriaService) {}

    public function index(Request $request): JsonResponse
    {
        $data = $this->categoriaService->listar($request->user()->empresa_id);
        return response()->json(['status' => 'success', 'data' => $data]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate(['nome' => 'required|string|max:100']);
        $categoria = $this->categoriaService->criar($request->user()->empresa_id, $request->all());
        return response()->json(['status' => 'success', 'data' => $categoria], 201);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $this->categoriaService->inativar($id, $request->user()->empresa_id);
        return response()->json(['status' => 'success', 'message' => 'Categoria inativada com sucesso.']);
    }
}