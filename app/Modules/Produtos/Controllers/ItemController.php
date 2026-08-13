<?php

namespace App\Modules\Produtos\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Produtos\Services\ItemService;
use App\Modules\Produtos\DTOs\ItemDTO;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ItemController extends Controller
{
    public function __construct(private ItemService $itemService) {}

    public function index(Request $request): JsonResponse
    {
        $empresaId = $request->user()->empresa_id;
        $itens = $this->itemService->listarItens($empresaId, $request->only(['busca', 'tipo']));

        return response()->json(['status' => 'success', 'data' => $itens]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'nome' => 'required|string|max:255',
            'tipo' => 'required|in:P,S,p,s',
            'preco_venda' => 'required|numeric|min:0',
            'preco_custo' => 'nullable|numeric|min:0',
            'ncm' => 'nullable|string|max:10',
    'cest' => 'nullable|string|max:10',
    'cfop' => 'nullable|string|max:5',
    'origem_mercadoria' => 'nullable|integer|between:0,8',
        ]);

        $empresaId = $request->user()->empresa_id;
        $dto = ItemDTO::fromRequest($request->all(), $empresaId);
        $item = $this->itemService->criarItem($dto);

        return response()->json(['status' => 'success', 'data' => $item], 201);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $empresaId = $request->user()->empresa_id;
        $item = $this->itemService->buscarPorId($id, $empresaId);

        if (! $item) {
            return response()->json(['status' => 'error', 'message' => 'Item não encontrado.'], 404);
        }

        return response()->json(['status' => 'success', 'data' => $item]);
    }

    public function update(Request $request, int $id): JsonResponse
{
    $empresaId = $request->user()->empresa_id;
    $item = $this->itemService->buscarPorId($id, $empresaId);

    if (! $item) {
        return response()->json(['status' => 'error', 'message' => 'Item não encontrado.'], 404);
    }

    $request->validate([
        'nome' => 'required|string|max:255',
        'tipo' => 'required|in:P,S,p,s',
        'preco_venda' => 'required|numeric|min:0',
        'preco_custo' => 'nullable|numeric|min:0',
        'ncm' => 'nullable|string|max:10',
    'cest' => 'nullable|string|max:10',
    'cfop' => 'nullable|string|max:5',
    'origem_mercadoria' => 'nullable|integer|between:0,8',
    ]);

    // Usa o fromUpdate para preservar o estoque_atual sem sobrescrever
    $dto = ItemDTO::fromUpdate($request->all(), $item, $empresaId);
    $itemAtualizado = $this->itemService->atualizarItem($item, $dto);

    return response()->json(['status' => 'success', 'data' => $itemAtualizado]);
}

    public function destroy(Request $request, int $id): JsonResponse
    {
        $empresaId = $request->user()->empresa_id;
        $item = $this->itemService->buscarPorId($id, $empresaId);

        if (! $item) {
            return response()->json(['status' => 'error', 'message' => 'Item não encontrado.'], 404);
        }

        $this->itemService->inativarItem($item);

        return response()->json(['status' => 'success', 'message' => 'Item inativado com sucesso.']);
    }
}