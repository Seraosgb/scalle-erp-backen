<?php

namespace App\Modules\OrdensServico\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Modules\Produtos\Models\Item;

class OrdemServicoItem extends Model
{
    protected $table = 'os_itens';

    protected $fillable = [
        'ordem_servico_id',
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