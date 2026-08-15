<?php

namespace App\Modules\RH\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use \App\Traits\BelongsToTenant;

class Vaga extends Model
{
    protected $table = 'rh_vagas';

    protected $fillable = [
        'empresa_id',
        'titulo',
        'departamento',
        'quantidade_vagas',
        'salario_proposto',
        'regime_contratacao',
        'status',
        'descricao',
        'requisitos',
    ];

    public function candidatos(): HasMany
    {
        return $this->hasMany(Candidato::class, 'vaga_id');
    }
}