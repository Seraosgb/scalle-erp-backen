<?php

namespace App\Modules\WMS\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Modules\Produtos\Models\Item;

class EstoqueDeposito extends Model
{
    protected $table = 'wms_estoque_deposito';

    protected $fillable = [
        'empresa_id',
        'deposito_id',
        'item_id',
        'quantidade',
    ];

    public function deposito(): BelongsTo
    {
        return $this->belongsTo(Deposito::class, 'deposito_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'item_id');
    }
}