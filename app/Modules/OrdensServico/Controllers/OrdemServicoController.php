<?php

namespace App\Modules\OrdensServico\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\OrdensServico\Services\OrdemServicoService;
use App\Modules\OrdensServico\DTOs\OrdemServicoDTO;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class OrdemServicoController extends Controller
{
    public function __construct(private OrdemServicoService $osService) {}

    public function index(Request $request): JsonResponse
    {
        $empresaId = $request->user()->empresa_id;
        $osList = $this->osService->listarOS($empresaId);

        return response()->json(['status' => 'success', 'data' => $osList]);
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
        $dto = OrdemServicoDTO::fromRequest($request->all(), $empresaId);
        $ordemServico = $this->osService->criarOS($dto);

        return response()->json(['status' => 'success', 'data' => $ordemServico], 201);
    }
    public function mudarStatus(Request $request, int $id): JsonResponse
{
    $request->validate([
        'status' => 'required|in:ABERTO,EM_ANDAMENTO,AGUARDANDO_PECA,CONCLUIDO,CANCELADO',
    ]);

    $empresaId = $request->user()->empresa_id;
    $status = strtoupper($request->status);

    $osAtualizada = $this->osService->atualizarStatus($id, $empresaId, $status);

    return response()->json([
        'status' => 'success',
        'message' => "Status da OS alterado para {$status} com sucesso.",
        'data' => $osAtualizada
    ]);
}
}