<?php

namespace App\Modules\Vendas\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Vendas\Services\VendaService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class VendaController extends Controller
{
    public function __construct(private VendaService $vendaService) {}

    public function index(Request $request): JsonResponse
    {
        $empresaId = $request->user()->empresa_id;
        $vendas = $this->vendaService->listarVendas($empresaId);

        return response()->json(['status' => 'success', 'data' => $vendas]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'cliente_id' => 'required|exists:pes_pessoas,id',
            'itens' => 'required|array|min:1',
            'itens.*.item_id' => 'required|exists:pro_itens,id',
            'itens.*.quantidade' => 'required|numeric|min:0.01',
        ]);

        $empresaId = $request->user()->empresa_id;
        $vendedorId = $request->user()->id;

        $venda = $this->vendaService->registrarVenda($empresaId, $vendedorId, $request->all());

        return response()->json(['status' => 'success', 'data' => $venda], 201);
    }
}