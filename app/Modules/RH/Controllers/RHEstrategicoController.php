<?php

namespace App\Modules\RH\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\RH\Models\Vaga;
use App\Modules\RH\Models\Candidato;
use App\Modules\RH\Models\AvaliacaoCiclo;
use App\Modules\RH\Models\AvaliacaoCriterio;
use App\Modules\RH\Models\AvaliacaoResposta;
use App\Modules\RH\Models\TreinamentoPdi;
use App\Modules\RH\Models\ClimaPesquisa;
use App\Modules\RH\Models\ClimaResposta;
use App\Modules\RH\Services\RecrutamentoService;
use App\Modules\RH\Services\ClimaService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class RHEstrategicoController extends Controller
{
    public function __construct(
        private RecrutamentoService $recrutamentoService,
        private ClimaService $climaService
    ) {}

    // --- Recrutamento & Seleção ---
    public function listarVagas(Request $request): JsonResponse
    {
        $vagas = Vaga::with('candidatos')->where('empresa_id', $request->user()->empresa_id)->get();
        return response()->json(['status' => 'success', 'data' => $vagas]);
    }

    public function criarVaga(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'titulo' => 'required|string|max:150',
            'departamento' => 'required|string|max:100',
            'quantidade_vagas' => 'nullable|integer|min:1',
            'salario_proposto' => 'nullable|numeric|min:0',
            'regime_contratacao' => 'required|in:CLT,PJ,ESTAGIO',
            'descricao' => 'nullable|string',
            'requisitos' => 'nullable|string',
        ]);

        $vaga = Vaga::create(array_merge($validated, ['empresa_id' => $request->user()->empresa_id]));

        return response()->json(['status' => 'success', 'message' => 'Vaga aberta com sucesso!', 'data' => $vaga], 201);
    }

    public function cadastrarCandidato(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'vaga_id' => 'required|exists:rh_vagas,id',
            'nome_completo' => 'required|string|max:200',
            'cpf' => 'nullable|string|max:14',
            'email' => 'nullable|email|max:150',
            'telefone' => 'nullable|string|max:30',
            'curriculo_resumo' => 'nullable|string',
        ]);

        $candidato = Candidato::create(array_merge($validated, ['empresa_id' => $request->user()->empresa_id, 'etapa_kanban' => 'INSCRITO']));

        return response()->json(['status' => 'success', 'message' => 'Candidato inscrito no funil com sucesso!', 'data' => $candidato], 201);
    }

    public function moverEtapaKanban(Request $request, int $candidatoId): JsonResponse
    {
        $validated = $request->validate([
            'etapa_kanban' => 'required|in:INSCRITO,TRIAGEM,ENTREVISTA,PROPOSTA,CONTRATADO,REPROVADO',
            'feedback_entrevista' => 'nullable|string',
        ]);

        $candidato = $this->recrutamentoService->atualizarEtapaKanban(
            candidatoId: $candidatoId,
            empresaId: $request->user()->empresa_id,
            novaEtapa: $validated['etapa_kanban'],
            feedback: $validated['feedback_entrevista'] ?? null
        );

        return response()->json(['status' => 'success', 'message' => 'Candidato movimentado no Kanban com sucesso!', 'data' => $candidato]);
    }

    // --- Avaliação de Desempenho ---
    public function criarCicloAvaliacao(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'titulo' => 'required|string|max:150',
            'data_inicio' => 'required|date',
            'data_fim' => 'required|date',
            'criterios' => 'required|array|min:1',
            'criterios.*.criterio' => 'required|string|max:150',
            'criterios.*.descricao' => 'nullable|string|max:255',
            'criterios.*.peso' => 'nullable|numeric|min:0.1',
        ]);

        $empresaId = $request->user()->empresa_id;

        $ciclo = AvaliacaoCiclo::create([
            'empresa_id' => $empresaId,
            'titulo' => $validated['titulo'],
            'data_inicio' => $validated['data_inicio'],
            'data_fim' => $validated['data_fim'],
            'status' => 'EM_ANDAMENTO',
        ]);

        foreach ($validated['criterios'] as $crit) {
            AvaliacaoCriterio::create([
                'empresa_id' => $empresaId,
                'ciclo_id' => $ciclo->id,
                'criterio' => $crit['criterio'],
                'descricao' => $crit['descricao'] ?? null,
                'peso' => (float) ($crit['peso'] ?? 1.00),
            ]);
        }

        return response()->json(['status' => 'success', 'message' => 'Ciclo de avaliação configurado!', 'data' => $ciclo->load('criterios')], 201);
    }

    public function responderAvaliacao(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ciclo_id' => 'required|exists:rh_avaliacao_ciclos,id',
            'colaborador_avaliado_id' => 'required|exists:rh_colaboradores,id',
            'avaliacoes' => 'required|array|min:1',
            'avaliacoes.*.criterio_id' => 'required|exists:rh_avaliacao_criterios,id',
            'avaliacoes.*.nota' => 'required|integer|min:1|max:5',
            'avaliacoes.*.comentario' => 'nullable|string',
        ]);

        $empresaId = $request->user()->empresa_id;
        $avaliadorId = $request->user()->id;

        foreach ($validated['avaliacoes'] as $item) {
            AvaliacaoResposta::updateOrCreate(
                [
                    'empresa_id' => $empresaId,
                    'ciclo_id' => $validated['ciclo_id'],
                    'colaborador_avaliado_id' => $validated['colaborador_avaliado_id'],
                    'avaliador_id' => $avaliadorId,
                    'criterio_id' => $item['criterio_id'],
                ],
                [
                    'nota' => $item['nota'],
                    'comentario' => $item['comentario'] ?? null,
                ]
            );
        }

        return response()->json(['status' => 'success', 'message' => 'Avaliação de desempenho salva com sucesso!']);
    }

    // --- PDI & Treinamentos ---
    public function registrarTreinamento(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'colaborador_id' => 'required|exists:rh_colaboradores,id',
            'titulo_treinamento' => 'required|string|max:150',
            'instituicao' => 'nullable|string|max:100',
            'carga_horaria_horas' => 'nullable|integer|min:0',
            'objetivo_pdi' => 'nullable|string',
        ]);

        $treinamento = TreinamentoPdi::create(array_merge($validated, ['empresa_id' => $request->user()->empresa_id]));

        return response()->json(['status' => 'success', 'message' => 'Plano de treinamento registrado com sucesso!', 'data' => $treinamento], 201);
    }

    // --- Pesquisa de Clima & eNPS (Anônimo) ---
    public function responderClima(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'pesquisa_id' => 'required|exists:rh_clima_pesquisas,id',
            'departamento' => 'nullable|string|max:100',
            'nota_enps' => 'required|integer|min:0|max:10',
            'comentario_anonimo' => 'nullable|string',
        ]);

        $resposta = ClimaResposta::create([
            'empresa_id' => $request->user()->empresa_id,
            'pesquisa_id' => $validated['pesquisa_id'],
            'departamento' => $validated['departamento'] ?? 'GERAL',
            'nota_enps' => $validated['nota_enps'],
            'comentario_anonimo' => $validated['comentario_anonimo'] ?? null,
        ]);

        return response()->json(['status' => 'success', 'message' => 'Opinião anônima registrada com sucesso!'], 201);
    }

    public function consultarEnps(Request $request, int $pesquisaId): JsonResponse
    {
        $resultado = $this->climaService->calcularIndicadoresEnps($pesquisaId, $request->user()->empresa_id);
        return response()->json(['status' => 'success', 'data' => $resultado]);
    }
    // --- Gestão de Pesquisas de Clima ---
    public function listarPesquisasClima(Request $request): JsonResponse
    {
        $pesquisas = ClimaPesquisa::withCount('respostas')
            ->where('empresa_id', $request->user()->empresa_id)
            ->orderBy('id', 'desc')
            ->get();

        return response()->json(['status' => 'success', 'data' => $pesquisas]);
    }

    public function criarPesquisaClima(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'titulo' => 'required|string|max:150',
            'data_inicio' => 'required|date',
            'data_fim' => 'required|date',
        ]);

        $pesquisa = ClimaPesquisa::create(array_merge($validated, [
            'empresa_id' => $request->user()->empresa_id,
            'status' => 'ABERTA'
        ]));

        return response()->json([
            'status' => 'success',
            'message' => 'Pesquisa de clima aberta com sucesso!',
            'data' => $pesquisa
        ], 201);
    }
}