<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Pessoas\Controllers\PessoaController;

Route::get('/', [PessoaController::class, 'index']);
Route::post('/', [PessoaController::class, 'store']);
Route::get('/{id}', [PessoaController::class, 'show']);
Route::put('/{id}', [PessoaController::class, 'update']);
Route::delete('/{id}', [PessoaController::class, 'destroy']);