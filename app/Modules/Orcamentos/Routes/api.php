<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Orcamentos\Controllers\OrcamentoController;

Route::get('/', [OrcamentoController::class, 'index']);
Route::post('/', [OrcamentoController::class, 'store']);
Route::post('/{id}/converter', [OrcamentoController::class, 'converter']);