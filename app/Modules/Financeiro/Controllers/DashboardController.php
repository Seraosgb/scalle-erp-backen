<?php

namespace App\Modules\Financeiro\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Financeiro\Services\DashboardService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function __construct(private DashboardService $dashboardService) {}

    public function resumo(Request $request): JsonResponse
    {
        $empresaId = $request->user()->empresa_id;
        $resumo = $this->dashboardService->obterResumo($empresaId);

        return response()->json([
            'status' => 'success',
            'data' => $resumo
        ]);
    }
}