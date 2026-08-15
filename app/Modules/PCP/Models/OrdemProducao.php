<?php

namespace App\Modules\PCP\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Modules\Produtos\Models\Item;
use App\Models\User;

class OrdemProducao extends Model
{
    protected $table = 'pcp_ordens_producao';

    protected $fillable = [
        'empresa_id',
        'produto_acabado_id',
        'responsavel_id',
        'numero_op',
        'status',
        'quantidade_planejada',
        'quantidade_produzida',
        'custo_total_insumos',
        'custo_total_adicional',
        'custo_total_producao',
        'custo_unitario_final',
        'data_inicio_prevista',
        'data_conclusao_prevista',
        'data_inicio_real',
        'data_conclusao_real',
        'observacoes',
    ];

    public function produtoAcabado(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'produto_acabado_id');
    }

    public function responsavel(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsavel_id');
    }

    public function perdas(): HasMany
    {
        return $this->hasMany(ApontamentoPerda::class, 'ordem_producao_id');
    }
}