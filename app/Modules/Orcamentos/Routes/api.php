<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Orcamentos\Controllers\OrcamentoController;
use App\Modules\Financeiro\Controllers\CategoriaFinanceiraController;

Route::get('/', [OrcamentoController::class, 'index']);
Route::post('/', [OrcamentoController::class, 'store']);
Route::post('/{id}/converter', [OrcamentoController::class, 'converter']);
// Categorias Financeiras
Route::get('/categorias', [CategoriaFinanceiraController::class, 'index']);
Route::post('/categorias', [CategoriaFinanceiraController::class, 'store']);
Route::delete('/categorias/{id}', [CategoriaFinanceiraController::class, 'destroy']);