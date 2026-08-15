<?php

namespace App\Modules\RH\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\RH\Models\Colaborador;
use App\Modules\RH\Models\Escala;
use App\Modules\RH\Models\Certificacao;
use App\Modules\RH\Services\PontoService;
use App\Modules\RH\Services\HoleriteService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class RHController extends Controller
{
    public function __construct(
        private PontoService $pontoService,
        private HoleriteService $holeriteService
    ) {}

    // --- Escalas ---
    public function listarEscalas(Request $request): JsonResponse
    {
        $escalas = Escala::where('empresa_id', $request->user()->empresa_id)->where('ativo', true)->get();
        return response()->json(['status' => 'success', 'data' => $escalas]);
    }

    public function criarEscala(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:100',
            'tipo_escala' => 'required|in:5X2,6X1,12X36,FLEXIVEL',
            'horas_diarias_padrao' => 'required|numeric|min:1|max:24',
            'tolerancia_minutos' => 'nullable|integer|min:0',
            'politica_extra' => 'nullable|in:BANCO_HORAS,PAGAMENTO_FOLHA',
        ]);

        $escala = Escala::create(array_merge($validated, ['empresa_id' => $request->user()->empresa_id, 'ativo' => true]));

        return response()->json(['status' => 'success', 'message' => 'Escala de trabalho cadastrada com sucesso!', 'data' => $escala], 201);
    }

    // --- Colaboradores ---
    public function listarColaboradores(Request $request): JsonResponse
    {
        $colaboradores = Colaborador::with(['escala', 'certificacoes', 'user'])
            ->where('empresa_id', $request->user()->empresa_id)
            ->orderBy('nome_completo')
            ->get();

        return response()->json(['status' => 'success', 'data' => $colaboradores]);
    }

    public function criarColaborador(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nome_completo' => 'required|string|max:200',
            'cpf' => 'required|string|max:14',
            'cargo' => 'required|string|max:100',
            'departamento' => 'nullable|string|max:100',
            'data_admissao' => 'required|date',
            'salario_base' => 'required|numeric|min:0',
            'tipo_contrato' => 'required|in:CLT,PJ,ESTAGIO,AUTONOMO',
            'escala_id' => 'nullable|exists:rh_escalas,id',
            'user_id' => 'nullable|exists:users,id',
        ]);

        $empresaId = $request->user()->empresa_id;
        $matricula = "MAT-" . date('Y') . "-" . str_pad((string) (Colaborador::where('empresa_id', $empresaId)->count() + 1), 4, '0', STR_PAD_LEFT);

        $colaborador = Colaborador::create(array_merge($validated, [
            'empresa_id' => $empresaId,
            'matricula' => $matricula,
            'status' => 'ATIVO'
        ]));

        return response()->json(['status' => 'success', 'message' => 'Colaborador admitido com sucesso!', 'data' => $colaborador], 201);
    }

    // --- Certificações ---
    public function registrarCertificacao(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'colaborador_id' => 'required|exists:rh_colaboradores,id',
            'nome_certificacao' => 'required|string|max:100',
            'numero_registro' => 'nullable|string|max:100',
            'data_emissao' => 'required|date',
            'data_validade' => 'required|date',
            'orgao_emissor' => 'nullable|string|max:100',
        ]);

        $cert = Certificacao::create(array_merge($validated, ['empresa_id' => $request->user()->empresa_id]));

        return response()->json(['status' => 'success', 'message' => 'Certificação registrada com sucesso!', 'data' => $cert], 201);
    }

    // --- Ponto Georreferenciado ---
    public function baterPonto(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'colaborador_id' => 'required|exists:rh_colaboradores,id',
            'latitude' => 'nullable|string',
            'longitude' => 'nullable|string',
        ]);

        $ponto = $this->pontoService->baterPonto(
            empresaId: $request->user()->empresa_id,
            colaboradorId: $validated['colaborador_id'],
            lat: $validated['latitude'] ?? null,
            long: $validated['longitude'] ?? null,
            ip: $request->ip()
        );

        return response()->json(['status' => 'success', 'message' => 'Ponto registrado com sucesso!', 'data' => $ponto], 201);
    }

    // --- Folha / Holerite ---
    public function gerarHolerite(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'colaborador_id' => 'required|exists:rh_colaboradores,id',
            'mes_ano_competencia' => 'required|string|max:7', // "2026-08"
            'proventos_adicionais' => 'nullable|array',
            'descontos_adicionais' => 'nullable|array',
        ]);

        $holerite = $this->holeriteService->gerarHoleriteCompetencia(
            empresaId: $request->user()->empresa_id,
            colaboradorId: $validated['colaborador_id'],
            competencia: $validated['mes_ano_competencia'],
            proventosAdicionais: $validated['proventos_adicionais'] ?? [],
            descontosAdicionais: $validated['descontos_adicionais'] ?? []
        );

        return response()->json(['status' => 'success', 'message' => 'Holerite gerado e despesa lançada no Contas a Pagar com sucesso!', 'data' => $holerite], 201);
    }
}