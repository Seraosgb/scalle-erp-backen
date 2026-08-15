<?php

namespace App\Modules\Compras\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Modules\Pessoas\Models\Pessoa;
use \App\Traits\BelongsToTenant;

class Compra extends Model
{
    protected $table = 'com_compras';

    protected $fillable = [
        'empresa_id',
        'fornecedor_id',
        'numero_nota',
        'status',
        'data_compra',
        'valor_total',
        'observacoes',
    ];

    public function fornecedor(): BelongsTo
    {
        return $this->belongsTo(Pessoa::class, 'fornecedor_id');
    }

    public function itens(): HasMany
    {
        return $this->hasMany(CompraItem::class, 'compra_id');
    }
}