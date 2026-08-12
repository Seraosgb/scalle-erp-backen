<?php

use Illuminate\Support\Facades\Route;

// Rotas PÚBLICAS de Autenticação
Route::prefix('v1/auth')->group(base_path('app/Modules/Auth/Routes/api.php'));

// Rotas PROTEGIDAS do Módulo de Pessoas (Exige Token Sanctum)
Route::middleware('auth:sanctum')
     ->prefix('v1/pessoas')
     ->group(base_path('app/Modules/Pessoas/Routes/api.php'));

// Rotas PROTEGIDAS do Módulo de Produtos e Serviços
Route::middleware('auth:sanctum')
     ->prefix('v1/produtos')
     ->group(base_path('app/Modules/Produtos/Routes/api.php'));

// Rotas PROTEGIDAS do Módulo de Ordens de Serviço
Route::middleware('auth:sanctum')
     ->prefix('v1/ordens-servico')
     ->group(base_path('app/Modules/OrdensServico/Routes/api.php'));
// Rotas PROTEGIDAS do Módulo Financeiro
Route::middleware('auth:sanctum')
     ->prefix('v1/financeiro/lancamentos')
     ->group(base_path('app/Modules/Financeiro/Routes/api.php'));
     // Rotas PROTEGIDAS do Módulo de Compras
Route::middleware('auth:sanctum')
     ->prefix('v1/compras')
     ->group(base_path('app/Modules/Compras/Routes/api.php'));