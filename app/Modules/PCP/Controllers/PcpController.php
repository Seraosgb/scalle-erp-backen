<?php

namespace App\Modules\PCP\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\PCP\Services\PcpService;
use App\Modules\PCP\Models\OrdemProducao;
use App\Modules\PCP\Models\FichaTecnica;
use App\Modules\PCP\Models\MotivoPerda;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PcpController extends Controller
{
    public function __construct(private PcpService $pcpService) {}

    // --- Ficha Técnica (BOM) ---
    public function obterFichaTecnica(Request $request, int $produtoId): JsonResponse
    {
        $fichas = FichaTecnica::with('insumo')
            ->where('empresa_id', $request->user()->empresa_id)
            ->where('produto_pai_id', $produtoId)
            ->get();

        return response()->json(['status' => 'success', 'data' => $fichas]);
    }

    public function salvarFichaTecnica(Request $request, int $produtoId): JsonResponse
    {
        $request->validate([
            'componentes' => 'required|array|min:1',
            'componentes.*.quantidade_necessaria' => 'required|numeric|min:0.0001',
            'componentes.*.tipo_componente' => 'nullable|in:INSUMO,MAO_DE_OBRA,CUSTO_INDIRETO',
        ]);

        $empresaId = $request->user()->empresa_id;
        $fichas = $this->pcpService->salvarFichaTecnica($empresaId, $produtoId, $request->componentes);

        return response()->json([
            'status' => 'success',
            'message' => 'Ficha técnica (BOM) atualizada com sucesso!',
            'data' => $fichas
        ]);
    }

    // --- Ordens de Produção (OP) ---
    public function listarOPs(Request $request): JsonResponse
    {
        $ops = OrdemProducao::with(['produtoAcabado', 'responsavel', 'perdas.motivo'])
            ->where('empresa_id', $request->user()->empresa_id)
            ->orderBy('id', 'desc')
            ->paginate(15);

        return response()->json(['status' => 'success', 'data' => $ops]);
    }

    public function criarOP(Request $request): JsonResponse
    {
        $request->validate([
            'produto_acabado_id' => 'required|exists:pro_itens,id',
            'quantidade_planejada' => 'required|numeric|min:0.0001',
        ]);

        $empresaId = $request->user()->empresa_id;
        $responsavelId = $request->user()->id;

        $op = $this->pcpService->criarOP($empresaId, $responsavelId, $request->all());

        return response()->json(['status' => 'success', 'data' => $op], 201);
    }

    public function atualizarStatus(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'status' => 'required|in:PLANEJADA,EM_PRODUCAO,CONCLUIDA,CANCELADA',
            'quantidade_produzida' => 'nullable|numeric|min:0.0001',
        ]);

        $empresaId = $request->user()->empresa_id;

        try {
            $op = $this->pcpService->atualizarStatusOP(
                $id,
                $empresaId,
                strtoupper($request->status),
                $request->quantidade_produzida ? (float) $request->quantidade_produzida : null
            );

            return response()->json([
                'status' => 'success',
                'message' => "Status da OP alterado para {$request->status} com sucesso.",
                'data' => $op
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }
    }

    // --- Apontamento de Perdas ---
    public function registrarPerda(Request $request, int $opId): JsonResponse
    {
        $request->validate([
            'insumo_id' => 'required|exists:pro_itens,id',
            'quantidade_perdida' => 'required|numeric|min:0.0001',
            'motivo_perda_id' => 'nullable|exists:pcp_motivos_perda,id',
        ]);

        $empresaId = $request->user()->empresa_id;
        $perda = $this->pcpService->registrarPerda($empresaId, $opId, $request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Apontamento de refugo/perda registrado com sucesso!',
            'data' => $perda
        ], 201);
    }

    // --- Motivos de Perda ---
    public function listarMotivosPerda(Request $request): JsonResponse
    {
        $motivos = MotivoPerda::where('empresa_id', $request->user()->empresa_id)
            ->where('ativo', true)
            ->orderBy('nome')
            ->get();

        return response()->json(['status' => 'success', 'data' => $motivos]);
    }

    public function criarMotivoPerda(Request $request): JsonResponse
    {
        $request->validate(['nome' => 'required|string|max:100']);

        $motivo = MotivoPerda::create([
            'empresa_id' => $request->user()->empresa_id,
            'nome' => $request->nome,
            'ativo' => true,
        ]);

        return response()->json(['status' => 'success', 'data' => $motivo], 201);
    }
}