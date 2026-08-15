<?php

namespace App\Modules\WMS\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\User;

class Transferencia extends Model
{
    protected $table = 'wms_transferencias';

    protected $fillable = [
        'empresa_id',
        'deposito_origem_id',
        'deposito_destino_id',
        'solicitante_id',
        'recebedor_id',
        'numero_transferencia',
        'status',
        'observacoes',
        'data_envio',
        'data_recebimento',
    ];

    public function depositoOrigem(): BelongsTo
    {
        return $this->belongsTo(Deposito::class, 'deposito_origem_id');
    }

    public function depositoDestino(): BelongsTo
    {
        return $this->belongsTo(Deposito::class, 'deposito_destino_id');
    }

    public function solicitante(): BelongsTo
    {
        return $this->belongsTo(User::class, 'solicitante_id');
    }

    public function itens(): HasMany
    {
        return $this->hasMany(TransferenciaItem::class, 'transferencia_id');
    }
}