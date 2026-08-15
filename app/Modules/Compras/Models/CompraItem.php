<?php

namespace App\Modules\Compras\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Modules\Produtos\Models\Item;
use \App\Traits\BelongsToTenant;

class CompraItem extends Model
{
    protected $table = 'com_itens';

    protected $fillable = [
        'compra_id',
        'item_id',
        'quantidade',
        'valor_unitario',
        'valor_subtotal',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'item_id');
    }
}