<?php

namespace App\Modules\Financeiro\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Financeiro\Services\ExportacaoContabilService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class ExportacaoContabilController extends Controller
{
    public function __construct(
        private ExportacaoContabilService $exportacaoService
    ) {}

    public function exportar(Request $request): JsonResponse|Response
    {
        $validated = $request->validate([
            'data_inicio' => 'required|date',
            'data_fim' => 'required|date|after_or_equal:data_inicio',
            'formato' => 'nullable|in:JSON,CSV,json,csv',
        ]);

        $formato = strtoupper($validated['formato'] ?? 'JSON');
        $empresaId = $request->user()->empresa_id;

        $resultado = $this->exportacaoService->exportarMovimentacaoPeriodo(
            empresaId: $empresaId,
            dataInicio: $validated['data_inicio'],
            dataFim: $validated['data_fim'],
            formato: $formato
        );

        if ($formato === 'CSV') {
            $fileName = "exportacao_contabil_{$validated['data_inicio']}_{$validated['data_fim']}.csv";
            return response($resultado, 200, [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Movimentação contábil exportada com sucesso!',
            'data' => $resultado
        ]);
    }
}