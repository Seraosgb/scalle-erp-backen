<?php

namespace App\Modules\RH\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use \App\Traits\BelongsToTenant;

class Ponto extends Model
{
    protected $table = 'rh_pontos';

    protected $fillable = [
        'empresa_id',
        'colaborador_id',
        'data_referencia',
        'entrada_1',
        'saida_1',
        'entrada_2',
        'saida_2',
        'total_horas_trabalhadas',
        'saldo_horas_dia',
        'latitude',
        'longitude',
        'ip_registro',
        'status_aprovacao',
    ];

    public function colaborador(): BelongsTo
    {
        return $this->belongsTo(Colaborador::class, 'colaborador_id');
    }
}