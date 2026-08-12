<?php

namespace App\Modules\Produtos\Models;

use Illuminate\Database\Eloquent\Model;

class Categoria extends Model
{
    protected $table = 'pro_categorias';

    protected $fillable = [
        'empresa_id',
        'nome',
        'ativo',
    ];
}