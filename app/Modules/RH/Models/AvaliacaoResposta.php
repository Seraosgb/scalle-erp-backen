<?php

namespace App\Modules\RH\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;
use \App\Traits\BelongsToTenant;

class AvaliacaoResposta extends Model
{
    protected $table = 'rh_avaliacao_respostas';

    protected $fillable = [
        'empresa_id',
        'ciclo_id',
        'colaborador_avaliado_id',
        'avaliador_id',
        'criterio_id',
        'nota',
        'comentario',
    ];

    public function avaliador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'avaliador_id');
    }

    public function colaboradorAvaliado(): BelongsTo
    {
        return $this->belongsTo(Colaborador::class, 'colaborador_avaliado_id');
    }

    public function criterio(): BelongsTo
    {
        return $this->belongsTo(AvaliacaoCriterio::class, 'criterio_id');
    }
}