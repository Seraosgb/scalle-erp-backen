<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Financeiro\Controllers\LancamentoFinanceiroController;

Route::get('/', [LancamentoFinanceiroController::class, 'index']);
Route::get('/resumo-dre', [LancamentoFinanceiroController::class, 'resumoDRE']);
Route::patch('/{id}/baixar', [LancamentoFinanceiroController::class, 'baixar']);