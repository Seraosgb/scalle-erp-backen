<?php

namespace App\Modules\RH\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Modules\Financeiro\Models\LancamentoFinanceiro;

class Holerite extends Model
{
    protected $table = 'rh_holerites';

    protected $fillable = [
        'empresa_id',
        'colaborador_id',
        'lancamento_financeiro_id',
        'mes_ano_competencia',
        'proventos_total',
        'descontos_total',
        'valor_liquido',
        'itens_discriminados',
        'status',
    ];

    protected $casts = [
        'itens_discriminados' => 'array',
    ];

    public function colaborador(): BelongsTo
    {
        return $this->belongsTo(Colaborador::class, 'colaborador_id');
    }

    public function lancamentoFinanceiro(): BelongsTo
    {
        return $this->belongsTo(LancamentoFinanceiro::class, 'lancamento_financeiro_id');
    }
}