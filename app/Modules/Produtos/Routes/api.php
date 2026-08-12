<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Produtos\Controllers\ItemController;
use App\Modules\Produtos\Controllers\CategoriaController;
use App\Modules\Produtos\Controllers\UnidadeController;

Route::get('/', [ItemController::class, 'index']);
Route::post('/', [ItemController::class, 'store']);
Route::get('/{id}', [ItemController::class, 'show']);
Route::put('/{id}', [ItemController::class, 'update']);
Route::delete('/{id}', [ItemController::class, 'destroy']);
// Categorias
Route::get('/categorias', [CategoriaController::class, 'index']);
Route::post('/categorias', [CategoriaController::class, 'store']);
Route::delete('/categorias/{id}', [CategoriaController::class, 'destroy']);

// Unidades
Route::get('/unidades', [UnidadeController::class, 'index']);
Route::post('/unidades', [UnidadeController::class, 'store']);
Route::delete('/unidades/{id}', [UnidadeController::class, 'destroy']);