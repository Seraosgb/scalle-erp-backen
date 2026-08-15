<?php

namespace App\Modules\RH\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use \App\Traits\BelongsToTenant;

class AvaliacaoCriterio extends Model
{
    protected $table = 'rh_avaliacao_criterios';

    protected $fillable = [
        'empresa_id',
        'ciclo_id',
        'criterio',
        'descricao',
        'peso',
    ];

    public function ciclo(): BelongsTo
    {
        return $this->belongsTo(AvaliacaoCiclo::class, 'ciclo_id');
    }
}