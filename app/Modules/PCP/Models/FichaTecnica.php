<?php

namespace App\Modules\PCP\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Modules\Produtos\Models\Item;

class FichaTecnica extends Model
{
    protected $table = 'pcp_fichas_tecnicas';

    protected $fillable = [
        'empresa_id',
        'produto_pai_id',
        'insumo_id',
        'tipo_componente',
        'descricao_custo',
        'quantidade_necessaria',
        'custo_estimado',
    ];

    public function insumo(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'insumo_id');
    }

    public function produtoPai(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'produto_pai_id');
    }
}