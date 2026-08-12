<?php

namespace App\Modules\Orcamentos\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Orcamentos\Services\OrcamentoService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class OrcamentoController extends Controller
{
    public function __construct(private OrcamentoService $orcamentoService) {}

    public function index(Request $request): JsonResponse
    {
        $empresaId = $request->user()->empresa_id;
        $orcamentos = $this->orcamentoService->listarOrcamentos($empresaId);

        return response()->json(['status' => 'success', 'data' => $orcamentos]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'cliente_id' => 'required|exists:pes_pessoas,id',
            'data_validade' => 'nullable|date',
            'itens' => 'required|array|min:1',
            'itens.*.item_id' => 'required|exists:pro_itens,id',
            'itens.*.quantidade' => 'required|numeric|min:0.01',
        ]);

        $empresaId = $request->user()->empresa_id;
        $vendedorId = $request->user()->id;

        $orcamento = $this->orcamentoService->criarOrcamento($empresaId, $vendedorId, $request->all());

        return response()->json(['status' => 'success', 'data' => $orcamento], 201);
    }

    public function converter(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'destino' => 'required|in:VENDA,OS,venda,os',
        ]);

        $empresaId = $request->user()->empresa_id;
        $destino = strtoupper($request->destino);

        try {
            $resultado = $this->orcamentoService->converterOrcamento($id, $empresaId, $destino);

            return response()->json([
                'status' => 'success',
                'message' => "Orçamento convertido em {$destino} com sucesso!",
                'data' => $resultado
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }
    }
}