<?php

namespace App\Modules\RH\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClimaPesquisa extends Model
{
    protected $table = 'rh_clima_pesquisas';

    protected $fillable = [
        'empresa_id',
        'titulo',
        'data_inicio',
        'data_fim',
        'status',
    ];

    public function respostas(): HasMany
    {
        return $this->hasMany(ClimaResposta::class, 'pesquisa_id');
    }
}