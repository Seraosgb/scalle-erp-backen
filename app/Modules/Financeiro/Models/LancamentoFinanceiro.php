<?php

namespace App\Modules\Financeiro\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Modules\Pessoas\Models\Pessoa;
use App\Modules\OrdensServico\Models\OrdemServico;
use \App\Traits\BelongsToTenant;

class LancamentoFinanceiro extends Model
{
    protected $table = 'fin_lancamentos';

    protected $fillable = [
        'empresa_id',
        'pessoa_id',
        'categoria_id',
        'ordem_servico_id',
        'tipo',
        'descricao',
        'valor',
        'data_vencimento',
        'data_pagamento',
        'status',
        'forma_pagamento',
        'parcela_atual',
        'total_parcelas',
        'observacoes',
    ];

    public function pessoa(): BelongsTo
    {
        return $this->belongsTo(Pessoa::class, 'pessoa_id');
    }

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(CategoriaFinanceira::class, 'categoria_id');
    }

    public function ordemServico(): BelongsTo
    {
        return $this->belongsTo(OrdemServico::class, 'ordem_servico_id');
    }
}