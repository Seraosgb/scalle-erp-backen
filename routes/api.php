<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ImpressaoController;

// Rotas PÚBLICAS de Autenticação
Route::prefix('v1/auth')->group(base_path('app/Modules/Auth/Routes/api.php'));

// ROTAS PROTEGIDAS POR SANCTUM + PERFIS (ACL)

// 👥 Pessoas: Todos os perfis podem visualizar e cadastrar
Route::middleware(['auth:sanctum', 'role:ADMIN,FINANCEIRO,TECNICO,ATENDENTE'])
     ->prefix('v1/pessoas')
     ->group(base_path('app/Modules/Pessoas/Routes/api.php'));

// 📦 Produtos e Serviços: Todos os perfis podem visualizar
Route::middleware(['auth:sanctum', 'role:ADMIN,FINANCEIRO,TECNICO,ATENDENTE'])
     ->prefix('v1/produtos')
     ->group(base_path('app/Modules/Produtos/Routes/api.php'));

// 🛠️ Ordens de Serviço: Apenas ADMIN, TECNICO e ATENDENTE
Route::middleware(['auth:sanctum', 'role:ADMIN,TECNICO,ATENDENTE'])
     ->prefix('v1/ordens-servico')
     ->group(base_path('app/Modules/OrdensServico/Routes/api.php'));

// 🛒 Compras: Restrito a ADMIN e FINANCEIRO
Route::middleware(['auth:sanctum', 'role:ADMIN,FINANCEIRO'])
     ->prefix('v1/compras')
     ->group(base_path('app/Modules/Compras/Routes/api.php'));

// 🏬 Vendas Diretas: ADMIN, FINANCEIRO e ATENDENTE
Route::middleware(['auth:sanctum', 'role:ADMIN,FINANCEIRO,ATENDENTE'])
     ->prefix('v1/vendas')
     ->group(base_path('app/Modules/Vendas/Routes/api.php'));

// 💰 Financeiro & DRE: Restrito a ADMIN e FINANCEIRO
Route::middleware(['auth:sanctum', 'role:ADMIN,FINANCEIRO'])
     ->prefix('v1/financeiro/lancamentos')
     ->group(base_path('app/Modules/Financeiro/Routes/api.php'));
     // 📋 Orçamentos: ADMIN, FINANCEIRO, TECNICO e ATENDENTE
Route::middleware(['auth:sanctum', 'role:ADMIN,FINANCEIRO,TECNICO,ATENDENTE'])
     ->prefix('v1/orcamentos')
     ->group(base_path('app/Modules/Orcamentos/Routes/api.php'));
     // 💰 Financeiro, DRE & Dashboard: Somente ADMIN e FINANCEIRO
Route::middleware(['auth:sanctum', 'role:ADMIN,FINANCEIRO'])
     ->prefix('v1/financeiro')
     ->group(function () {
         Route::get('/dashboard/resumo', [\App\Modules\Financeiro\Controllers\DashboardController::class, 'resumo']);
         Route::prefix('lancamentos')->group(base_path('app/Modules/Financeiro/Routes/api.php'));
     });
     // 🖨️ Rotas de Impressão de Orçamento e OS
Route::middleware('auth:sanctum')->group(function () {
    Route::get('v1/orcamentos/{id}/imprimir', [ImpressaoController::class, 'orcamento']);
    Route::get('v1/ordens-servico/{id}/imprimir', [ImpressaoController::class, 'ordemServico']);
});