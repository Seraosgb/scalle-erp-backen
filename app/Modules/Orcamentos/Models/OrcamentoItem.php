<?php

namespace App\Modules\Orcamentos\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Modules\Produtos\Models\Item;

class OrcamentoItem extends Model
{
    protected $table = 'orc_itens';

    protected $fillable = [
        'orcamento_id',
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