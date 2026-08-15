<?php

namespace App\Modules\PCP\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Modules\Produtos\Models\Item;

class ApontamentoPerda extends Model
{
    protected $table = 'pcp_apontamentos_perda';

    protected $fillable = [
        'empresa_id',
        'ordem_producao_id',
        'insumo_id',
        'motivo_perda_id',
        'quantidade_perdida',
        'custo_perda',
        'observacoes',
    ];

    public function insumo(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'insumo_id');
    }

    public function motivo(): BelongsTo
    {
        return $this->belongsTo(MotivoPerda::class, 'motivo_perda_id');
    }
}