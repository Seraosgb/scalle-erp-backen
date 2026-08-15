<?php

namespace App\Modules\Vendas\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Modules\Produtos\Models\Item;
use \App\Traits\BelongsToTenant;

class VendaItem extends Model
{
    protected $table = 'ven_itens';

    protected $fillable = [
        'venda_id',
        'item_id',
        'descricao_item',
        'quantidade',
        'valor_unitario',
        'valor_subtotal',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'item_id');
    }
}