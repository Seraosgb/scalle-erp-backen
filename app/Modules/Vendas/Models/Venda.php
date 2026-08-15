<?php

namespace App\Modules\Vendas\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Modules\Pessoas\Models\Pessoa;
use App\Models\User;
use \App\Traits\BelongsToTenant;

class Venda extends Model
{
    protected $table = 'ven_vendas';

    protected $fillable = [
        'empresa_id',
        'cliente_id',
        'vendedor_id',
        'numero_venda',
        'status',
        'data_venda',
        'valor_subtotal',
        'valor_desconto',
        'valor_total',
        'observacoes',
    ];

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Pessoa::class, 'cliente_id');
    }

    public function vendedor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'vendedor_id');
    }

    public function itens(): HasMany
    {
        return $this->hasMany(VendaItem::class, 'venda_id');
    }
}