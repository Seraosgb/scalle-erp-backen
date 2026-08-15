<?php

namespace App\Modules\WMS\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Modules\Produtos\Models\Item;

class TransferenciaItem extends Model
{
    protected $table = 'wms_transferencia_itens';

    protected $fillable = [
        'transferencia_id',
        'item_id',
        'quantidade',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'item_id');
    }
}