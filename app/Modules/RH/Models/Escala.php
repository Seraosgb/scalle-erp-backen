<?php

namespace App\Modules\RH\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Escala extends Model
{
    protected $table = 'rh_escalas';

    protected $fillable = [
        'empresa_id',
        'nome',
        'tipo_escala',
        'horas_diarias_padrao',
        'tolerancia_minutos',
        'politica_extra',
        'ativo',
    ];

    public function colaboradores(): HasMany
    {
        return $this->hasMany(Colaborador::class, 'escala_id');
    }
}