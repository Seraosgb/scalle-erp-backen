<?php

namespace App\Modules\Ativos\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;
use \App\Traits\BelongsToTenant;

class Cautela extends Model
{
    protected $table = 'pat_cautelas';

    protected $fillable = [
        'empresa_id',
        'ativo_id',
        'responsavel_entrega_id',
        'custodiante_id',
        'data_retirada',
        'data_devolucao_prevista',
        'data_devolucao_real',
        'status',
        'ip_assinatura',
        'motivo_uso',
        'observacoes_devolucao',
    ];

    public function ativo(): BelongsTo
    {
        return $this->belongsTo(Ativo::class, 'ativo_id');
    }

    public function responsavelEntrega(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsavel_entrega_id');
    }

    public function custodiante(): BelongsTo
    {
        return $this->belongsTo(User::class, 'custodiante_id');
    }
}