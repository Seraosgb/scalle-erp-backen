<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Compras\Controllers\CompraController;

Route::get('/', [CompraController::class, 'index']);
Route::post('/', [CompraController::class, 'store']);