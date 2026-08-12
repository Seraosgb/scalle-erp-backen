<?php

use Illuminate\Support\Facades\Route;
use App\Modules\OrdensServico\Controllers\OrdemServicoController;

Route::get('/', [OrdemServicoController::class, 'index']);
Route::post('/', [OrdemServicoController::class, 'store']);
Route::patch('/{id}/status', [OrdemServicoController::class, 'mudarStatus']);