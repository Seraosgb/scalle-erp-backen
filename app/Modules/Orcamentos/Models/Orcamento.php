<?php

namespace App\Modules\Orcamentos\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Modules\Pessoas\Models\Pessoa;
use App\Models\User;

class Orcamento extends Model
{
    protected $table = 'orc_orcamentos';

    protected $fillable = [
        'empresa_id',
        'cliente_id',
        'vendedor_id',
        'numero_orcamento',
        'status',
        'data_emissao',
        'data_validade',
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
        return $this->hasMany(OrcamentoItem::class, 'orcamento_id');
    }
}