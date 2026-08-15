<?php

namespace App\Modules\Ativos\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Ativos\Services\AtivoService;
use App\Modules\Ativos\Models\Ativo;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AtivoController extends Controller
{
    public function __construct(private AtivoService $ativoService) {}

    public function index(Request $request): JsonResponse
    {
        $ativos = $this->ativoService->listarAtivos($request->user()->empresa_id, $request->only(['categoria', 'status']));
        return response()->json(['status' => 'success', 'data' => $ativos]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:150',
            'categoria' => 'nullable|string|max:50',
            'item_id' => 'nullable|exists:pro_itens,id',
            'numero_serie' => 'nullable|string|max:100',
            'data_aquisicao' => 'nullable|date',
            'valor_aquisicao' => 'nullable|numeric|min:0',
            'taxa_depreciacao_anual' => 'nullable|numeric|min:0',
            'observacoes' => 'nullable|string',
        ]);

        $ativo = $this->ativoService->criarAtivo($request->user()->empresa_id, $validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Ativo patrimonial tombado com sucesso!',
            'data' => $ativo
        ], 201);
    }

    public function emitirCautela(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ativo_id' => 'required|exists:pat_ativos,id',
            'custodiante_id' => 'required|exists:users,id',
            'data_devolucao_prevista' => 'nullable|date',
            'motivo_uso' => 'nullable|string',
        ]);

        try {
            $cautela = $this->ativoService->emitirTermoCautela(
                empresaId: $request->user()->empresa_id,
                responsavelEntregaId: $request->user()->id,
                data: $validated,
                ip: $request->ip()
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Termo de cautela emitido e ativo entregue ao técnico com sucesso!',
                'data' => $cautela
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }
    }

    public function devolverCautela(Request $request, int $cautelaId): JsonResponse
    {
        $validated = $request->validate([
            'status_devolucao' => 'nullable|in:DEVOLVIDO,AVARIADO,devolvido,avariado',
            'observacoes_devolucao' => 'nullable|string',
        ]);

        try {
            $cautela = $this->ativoService->devolverCautela(
                cautelaId: $cautelaId,
                empresaId: $request->user()->empresa_id,
                data: $validated
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Devolução do ativo concluída com sucesso!',
                'data' => $cautela
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }
    }

    public function gerarQrCode(Request $request, int $id): JsonResponse
    {
        $ativo = Ativo::where('id', $id)->where('empresa_id', $request->user()->empresa_id)->firstOrFail();
        $payloadQr = "SCALLE:PAT:{$ativo->empresa_id}:{$ativo->codigo_patrimonio}";

        return response()->json([
            'status' => 'success',
            'data' => [
                'ativo_id' => $ativo->id,
                'codigo_patrimonio' => $ativo->codigo_patrimonio,
                'payload' => $payloadQr,
                'qr_code_url' => "https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=" . urlencode($payloadQr)
            ]
        ]);
    }
}