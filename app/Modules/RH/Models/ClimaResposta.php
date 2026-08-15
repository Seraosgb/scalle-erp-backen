<?php

namespace App\Modules\RH\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use \App\Traits\BelongsToTenant;

class ClimaResposta extends Model
{
    protected $table = 'rh_clima_respostas';

    protected $fillable = [
        'empresa_id',
        'pesquisa_id',
        'departamento',
        'nota_enps',
        'comentario_anonimo',
    ];

    public function pesquisa(): BelongsTo
    {
        return $this->belongsTo(ClimaPesquisa::class, 'pesquisa_id');
    }
}