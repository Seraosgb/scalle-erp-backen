<?php

namespace App\Modules\Fiscal\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Fiscal\Services\FiscalService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class FiscalController extends Controller
{
    public function __construct(private FiscalService $fiscalService) {}

    public function emitirNFe(Request $request, int $vendaId): JsonResponse
    {
        $empresaId = $request->user()->empresa_id;

        try {
            $doc = $this->fiscalService->emitirNFeVenda($vendaId, $empresaId);

            return response()->json([
                'status' => 'success',
                'message' => 'NFe emitida e autorizada com sucesso!',
                'data' => $doc
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }
    }

    public function emitirNFSe(Request $request, int $osId): JsonResponse
    {
        $empresaId = $request->user()->empresa_id;

        try {
            $doc = $this->fiscalService->emitirNFSeOS($osId, $empresaId);

            return response()->json([
                'status' => 'success',
                'message' => 'NFS-e emitida e autorizada com sucesso!',
                'data' => $doc
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }
    }
}