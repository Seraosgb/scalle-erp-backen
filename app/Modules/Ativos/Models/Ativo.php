<?php

namespace App\Modules\Ativos\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\User;
use App\Modules\Produtos\Models\Item;
use \App\Traits\BelongsToTenant;

class Ativo extends Model
{
    protected $table = 'pat_ativos';

    protected $fillable = [
        'empresa_id',
        'item_id',
        'custodiante_atual_id',
        'codigo_patrimonio',
        'nome',
        'categoria',
        'numero_serie',
        'data_aquisicao',
        'valor_aquisicao',
        'taxa_depreciacao_anual',
        'status',
        'observacoes',
        'ativo',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    public function custodianteAtual(): BelongsTo
    {
        return $this->belongsTo(User::class, 'custodiante_atual_id');
    }

    public function cautelas(): HasMany
    {
        return $this->hasMany(Cautela::class, 'ativo_id');
    }

    public function getValorResidualAttribute(): float
    {
        if (!$this->data_aquisicao || $this->taxa_depreciacao_anual <= 0) {
            return (float) $this->valor_aquisicao;
        }

        $anos = (float) now()->diffInDays($this->data_aquisicao) / 365.25;
        $depreciacaoTotal = (float) $this->valor_aquisicao * (($this->taxa_depreciacao_anual / 100) * $anos);

        return max(0.00, round((float) $this->valor_aquisicao - $depreciacaoTotal, 2));
    }
}