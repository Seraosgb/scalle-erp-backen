<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Produtos\Controllers\ItemController;

Route::get('/', [ItemController::class, 'index']);
Route::post('/', [ItemController::class, 'store']);
Route::get('/{id}', [ItemController::class, 'show']);
Route::put('/{id}', [ItemController::class, 'update']);
Route::delete('/{id}', [ItemController::class, 'destroy']);