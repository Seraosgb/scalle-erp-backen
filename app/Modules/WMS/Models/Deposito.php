<?php

namespace App\Modules\WMS\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\User;

class Deposito extends Model
{
    protected $table = 'wms_depositos';

    protected $fillable = [
        'empresa_id',
        'responsavel_id',
        'nome',
        'tipo',
        'is_padrao',
        'ativo',
    ];

    public function responsavel(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsavel_id');
    }

    public function estoques(): HasMany
    {
        return $this->hasMany(EstoqueDeposito::class, 'deposito_id');
    }
}