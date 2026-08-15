<?php

namespace App\Modules\Produtos\Models;

use Illuminate\Database\Eloquent\Model;
use \App\Traits\BelongsToTenant;

class Unidade extends Model
{
    protected $table = 'pro_unidades';

    protected $fillable = [
        'empresa_id',
        'sigla',
        'nome',
        'ativo',
    ];
}