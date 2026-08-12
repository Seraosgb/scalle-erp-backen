<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Vendas\Controllers\VendaController;

Route::get('/', [VendaController::class, 'index']);
Route::post('/', [VendaController::class, 'store']);