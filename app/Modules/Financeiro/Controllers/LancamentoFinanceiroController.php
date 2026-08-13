<?php

namespace App\Modules\Financeiro\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Financeiro\Services\LancamentoFinanceiroService;
use App\Modules\Financeiro\Services\PixService;
use App\Modules\Financeiro\Models\LancamentoFinanceiro;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class LancamentoFinanceiroController extends Controller
{
    // Apenas UM construtor com todas as injeções de dependência
    public function __construct(
        private LancamentoFinanceiroService $financeiroService,
        private PixService $pixService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $empresaId = $request->user()->empresa_id;
        $filtros = $request->only(['tipo', 'status']);
        $lancamentos = $this->financeiroService->listarLancamentos($empresaId, $filtros);

        return response()->json(['status' => 'success', 'data' => $lancamentos]);
    }

    public function baixar(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'forma_pagamento' => 'required|in:PIX,DINHEIRO,CARTAO_CREDITO,CARTAO_DEBITO,BOLETO,TRANSFERENCIA',
            'data_pagamento' => 'nullable|date',
        ]);

        $empresaId = $request->user()->empresa_id;
        $formaPagamento = $request->forma_pagamento;
        $dataPagamento = $request->data_pagamento;

        $lancamentoAtualizado = $this->financeiroService->baixarLancamento(
            id: $id,
            empresaId: $empresaId,
            formaPagamento: $formaPagamento,
            dataPagamento: $dataPagamento
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Lançamento liquidado com sucesso.',
            'data' => $lancamentoAtualizado
        ]);
    }

    public function resumoDRE(Request $request): JsonResponse
    {
        $request->validate([
            'data_inicio' => 'nullable|date_format:Y-m-d',
            'data_fim' => 'nullable|date_format:Y-m-d|after_or_equal:data_inicio',
        ]);

        $empresaId = $request->user()->empresa_id;
        $resumo = $this->financeiroService->obterResumoDRE(
            empresaId: $empresaId,
            dataInicio: $request->data_inicio,
            dataFim: $request->data_fim
        );

        return response()->json(['status' => 'success', 'data' => $resumo]);
    }

    public function gerarPix(Request $request, int $id): JsonResponse
    {
        $empresaId = $request->user()->empresa_id;

        $lancamento = LancamentoFinanceiro::with('pessoa')
            ->where('id', $id)
            ->where('empresa_id', $empresaId)
            ->where('tipo', 'RECEITA')
            ->firstOrFail();

        if ($lancamento->status === 'PAGO') {
            return response()->json([
                'status' => 'error',
                'message' => 'Este lançamento já foi liquidado.'
            ], 422);
        }

        $chavePix = env('PIX_CHAVE_PADRAO', 'suachavepix@email.com');
        $copiaECola = $this->pixService->gerarPixCopiaECola($lancamento, $chavePix);

        return response()->json([
            'status' => 'success',
            'data' => [
                'lancamento_id' => $lancamento->id,
                'valor' => $lancamento->valor,
                'chave_pix' => $chavePix,
                'pix_copia_e_cola' => $copiaECola,
                'qr_code_url' => "https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=" . urlencode($copiaECola)
            ]
        ]);
    }
}