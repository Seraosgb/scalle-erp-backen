<?php

namespace App\Modules\RH\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use \App\Traits\BelongsToTenant;

class TreinamentoPdi extends Model
{
    protected $table = 'rh_treinamentos';

    protected $fillable = [
        'empresa_id',
        'colaborador_id',
        'titulo_treinamento',
        'instituicao',
        'carga_horaria_horas',
        'data_inicio',
        'data_conclusao',
        'status',
        'objetivo_pdi',
    ];

    public function colaborador(): BelongsTo
    {
        return $this->belongsTo(Colaborador::class, 'colaborador_id');
    }
}