<?php

namespace App\Modules\Frotas\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;
use App\Modules\Financeiro\Models\LancamentoFinanceiro;

class Abastecimento extends Model
{
    protected $table = 'fro_abastecimentos';

    protected $fillable = [
        'empresa_id',
        'veiculo_id',
        'motorista_id',
        'lancamento_financeiro_id',
        'data_abastecimento',
        'km_odometro',
        'litros',
        'valor_litro',
        'valor_total',
        'posto_combustivel',
        'observacoes',
    ];

    public function veiculo(): BelongsTo
    {
        return $this->belongsTo(Veiculo::class, 'veiculo_id');
    }

    public function motorista(): BelongsTo
    {
        return $this->belongsTo(User::class, 'motorista_id');
    }

    public function lancamentoFinanceiro(): BelongsTo
    {
        return $this->belongsTo(LancamentoFinanceiro::class, 'lancamento_financeiro_id');
    }
}