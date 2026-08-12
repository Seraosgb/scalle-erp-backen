<?php

namespace App\Modules\Financeiro\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Financeiro\Services\LancamentoFinanceiroService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class LancamentoFinanceiroController extends Controller
{
    public function __construct(private LancamentoFinanceiroService $financeiroService) {}

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
}