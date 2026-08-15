<?php

namespace App\Modules\RH\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AvaliacaoCiclo extends Model
{
    protected $table = 'rh_avaliacao_ciclos';

    protected $fillable = [
        'empresa_id',
        'titulo',
        'data_inicio',
        'data_fim',
        'status',
    ];

    public function criterios(): HasMany
    {
        return $this->hasMany(AvaliacaoCriterio::class, 'ciclo_id');
    }

    public function respostas(): HasMany
    {
        return $this->hasMany(AvaliacaoResposta::class, 'ciclo_id');
    }
}