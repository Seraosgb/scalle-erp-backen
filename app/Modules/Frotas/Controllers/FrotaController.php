<?php

namespace App\Modules\Frotas\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Frotas\Services\FrotaService;
use App\Modules\Fiscal\Contracts\FiscalDriverInterface;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class FrotaController extends Controller
{
    public function __construct(
        private FrotaService $frotaService,
        private FiscalDriverInterface $fiscalDriver
    ) {}

    public function listarVeiculos(Request $request): JsonResponse
    {
        $veiculos = $this->frotaService->listarVeiculos($request->user()->empresa_id);
        return response()->json(['status' => 'success', 'data' => $veiculos]);
    }

    public function criarVeiculo(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'placa' => 'required|string|max:10',
            'modelo' => 'required|string|max:100',
            'marca' => 'required|string|max:50',
            'ano_fabricacao' => 'nullable|integer',
            'combustivel_tipo' => 'nullable|string|max:30',
            'km_atual' => 'nullable|numeric|min:0',
            'motorista_padrao_id' => 'nullable|exists:users,id',
        ]);

        $veiculo = $this->frotaService->criarVeiculo($request->user()->empresa_id, $validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Veículo cadastrado na frota com sucesso!',
            'data' => $veiculo
        ], 201);
    }

    public function listarAbastecimentos(Request $request): JsonResponse
    {
        $abastecimentos = $this->frotaService->listarAbastecimentos(
            empresaId: $request->user()->empresa_id,
            veiculoId: $request->query('veiculo_id')
        );

        return response()->json(['status' => 'success', 'data' => $abastecimentos]);
    }

    public function registrarAbastecimento(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'veiculo_id' => 'required|exists:fro_veiculos,id',
            'km_odometro' => 'required|numeric|min:0',
            'litros' => 'required|numeric|min:0.01',
            'valor_litro' => 'required|numeric|min:0.01',
            'valor_total' => 'nullable|numeric|min:0.01',
            'posto_combustivel' => 'nullable|string|max:150',
            'observacoes' => 'nullable|string',
        ]);

        $empresaId = $request->user()->empresa_id;
        $motoristaId = $request->user()->id;

        $abastecimento = $this->frotaService->registrarAbastecimento($empresaId, $motoristaId, $validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Abastecimento registrado e despesa lançada no Contas a Pagar com sucesso!',
            'data' => $abastecimento
        ], 201);
    }

    public function emitirCTe(Request $request): JsonResponse
    {
        $doc = $this->fiscalDriver->emitirCTe($request->all());
        return response()->json(['status' => 'success', 'message' => 'CTe emitido com sucesso!', 'data' => $doc], 201);
    }
}