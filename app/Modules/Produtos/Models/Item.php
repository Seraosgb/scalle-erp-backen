<?php

namespace App\Modules\Produtos\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use \App\Traits\BelongsToTenant;

class Item extends Model
{
    protected $table = 'pro_itens';

    protected $fillable = [
    'empresa_id',
    'categoria_id',
    'unidade_id',
    'tipo',
    'codigo_sku',
    'codigo_barras',
    'nome',
    'descricao',
    'ncm',
    'cest',
    'cfop',
    'origem_mercadoria',
    'preco_custo',
    'preco_venda',
    'estoque_atual',
    'ativo',
];
    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class, 'categoria_id');
    }

    public function unidade(): BelongsTo
    {
        return $this->belongsTo(Unidade::class, 'unidade_id');
    }
}