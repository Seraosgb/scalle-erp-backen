<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plano extends Model
{
    protected $table = 'sis_planos';

    protected $fillable = [
        'nome',
        'slug',
        'preco_mensal',
        'limite_usuarios',
        'limite_storage_bytes',
        'modulos_habilitados',
        'ativo',
    ];

    protected $casts = [
        'modulos_habilitados' => 'array',
        'limite_storage_bytes' => 'integer',
        'limite_usuarios' => 'integer',
        'preco_mensal' => 'decimal:2',
        'ativo' => 'boolean',
    ];

    public function empresas(): HasMany
    {
        return $this->hasMany(Empresa::class, 'plano_id');
    }
}