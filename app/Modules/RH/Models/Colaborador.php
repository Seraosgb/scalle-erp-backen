<?php

namespace App\Modules\RH\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\User;
use \App\Traits\BelongsToTenant;

class Colaborador extends Model
{
    protected $table = 'rh_colaboradores';

    protected $fillable = [
        'empresa_id',
        'user_id',
        'escala_id',
        'matricula',
        'nome_completo',
        'cpf',
        'cargo',
        'departamento',
        'data_admissao',
        'data_demissao',
        'salario_base',
        'tipo_contrato',
        'status',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function escala(): BelongsTo
    {
        return $this->belongsTo(Escala::class, 'escala_id');
    }

    public function certificacoes(): HasMany
    {
        return $this->hasMany(Certificacao::class, 'colaborador_id');
    }

    public function pontos(): HasMany
    {
        return $this->hasMany(Ponto::class, 'colaborador_id');
    }

    public function bancoHoras(): HasMany
    {
        return $this->hasMany(BancoHoras::class, 'colaborador_id');
    }
}