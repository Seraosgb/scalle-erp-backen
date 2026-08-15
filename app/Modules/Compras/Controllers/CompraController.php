<?php

namespace App\Modules\Compras\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Compras\Services\CompraService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CompraController extends Controller
{
    public function __construct(private CompraService $compraService) {}

    public function index(Request $request): JsonResponse
    {
        $empresaId = $request->user()->empresa_id;
        $compras = $this->compraService->listarCompras($empresaId);

        return response()->json(['status' => 'success', 'data' => $compras]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'fornecedor_id' => 'required|exists:pes_pessoas,id',
            'itens' => 'required|array|min:1',
            'itens.*.item_id' => 'required|exists:pro_itens,id',
            'itens.*.quantidade' => 'required|numeric|min:0.01',
            'itens.*.valor_unitario' => 'required|numeric|min:0.00',
        ]);

        $empresaId = $request->user()->empresa_id;
        $compra = $this->compraService->registrarCompra($empresaId, $request->all());

        return response()->json(['status' => 'success', 'data' => $compra], 201);
    }
    public function importarXml(Request $request, \App\Modules\Compras\Services\NfeXmlImportService $importService): JsonResponse
    {
        $request->validate([
            'arquivo_xml' => 'required|file|mimes:xml,txt',
        ]);

        $empresaId = $request->user()->empresa_id;
        $conteudoXml = file_get_contents($request->file('arquivo_xml')->getRealPath());

        try {
            $compra = $importService->importarXml($empresaId, $conteudoXml);

            return response()->json([
                'status' => 'success',
                'message' => 'XML de compra importado, estoque abastecido e financeiro lançado com sucesso!',
                'data' => $compra
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }
    }
}