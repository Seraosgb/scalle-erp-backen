<?php

namespace App\Modules\Financeiro\Models;

use Illuminate\Database\Eloquent\Model;

class CategoriaFinanceira extends Model
{
    protected $table = 'fin_categorias';

    protected $fillable = [
        'empresa_id',
        'nome',
        'tipo',
        'ativo',
    ];
}