<?php

namespace App\Modules\WMS\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\WMS\Services\WmsService;
use App\Modules\WMS\Models\Deposito;
use App\Modules\WMS\Models\EstoqueDeposito;
use App\Modules\WMS\Models\Transferencia;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class WmsController extends Controller
{
    public function __construct(private WmsService $wmsService) {}

    // --- Depósitos ---
    public function listarDepositos(Request $request): JsonResponse
    {
        $depositos = Deposito::with('responsavel')
            ->where('empresa_id', $request->user()->empresa_id)
            ->where('ativo', true)
            ->orderBy('nome')
            ->get();

        return response()->json(['status' => 'success', 'data' => $depositos]);
    }

    public function criarDeposito(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:100',
            'tipo' => 'nullable|in:FISICO,VOLANTE_TECNICO,QUARENTENA_AVARIA',
            'responsavel_id' => 'nullable|exists:users,id',
            'is_padrao' => 'nullable|boolean',
        ]);

        $empresaId = $request->user()->empresa_id;

        if (!empty($validated['is_padrao']) && $validated['is_padrao']) {
            Deposito::where('empresa_id', $empresaId)->update(['is_padrao' => false]);
        }

        $deposito = Deposito::create(array_merge($validated, ['empresa_id' => $empresaId, 'ativo' => true]));

        return response()->json(['status' => 'success', 'data' => $deposito], 201);
    }

    public function consultarEstoquePorDeposito(Request $request, int $depositoId): JsonResponse
    {
        $empresaId = $request->user()->empresa_id;

        $estoque = EstoqueDeposito::with('item')
            ->where('empresa_id', $empresaId)
            ->where('deposito_id', $depositoId)
            ->get();

        return response()->json(['status' => 'success', 'data' => $estoque]);
    }

    // --- Transferências ---
    public function listarTransferencias(Request $request): JsonResponse
    {
        $transferencias = Transferencia::with(['depositoOrigem', 'depositoDestino', 'solicitante', 'itens.item'])
            ->where('empresa_id', $request->user()->empresa_id)
            ->orderBy('id', 'desc')
            ->paginate(15);

        return response()->json(['status' => 'success', 'data' => $transferencias]);
    }

    public function criarTransferencia(Request $request): JsonResponse
    {
        $request->validate([
            'deposito_origem_id' => 'required|exists:wms_depositos,id',
            'deposito_destino_id' => 'required|exists:wms_depositos,id',
            'modo' => 'nullable|in:DIRETO,TRANSITO,direto,transito',
            'itens' => 'required|array|min:1',
            'itens.*.item_id' => 'required|exists:pro_itens,id',
            'itens.*.quantidade' => 'required|numeric|min:0.0001',
        ]);

        $empresaId = $request->user()->empresa_id;
        $solicitanteId = $request->user()->id;

        try {
            $trf = $this->wmsService->criarTransferencia($empresaId, $solicitanteId, $request->all());

            return response()->json([
                'status' => 'success',
                'message' => 'Transferência registrada com sucesso!',
                'data' => $trf
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }
    }

    public function concluirTransferencia(Request $request, int $id): JsonResponse
    {
        $empresaId = $request->user()->empresa_id;
        $recebedorId = $request->user()->id;

        try {
            $trf = $this->wmsService->concluirTransferencia($id, $empresaId, $recebedorId);

            return response()->json([
                'status' => 'success',
                'message' => 'Transferência concluída e estoque recebido com sucesso!',
                'data' => $trf
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }
    }
}