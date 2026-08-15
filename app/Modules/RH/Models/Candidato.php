<?php

namespace App\Modules\RH\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use \App\Traits\BelongsToTenant;

class Candidato extends Model
{
    protected $table = 'rh_candidatos';

    protected $fillable = [
        'empresa_id',
        'vaga_id',
        'nome_completo',
        'cpf',
        'email',
        'telefone',
        'etapa_kanban',
        'curriculo_resumo',
        'feedback_entrevista',
    ];

    public function vaga(): BelongsTo
    {
        return $this->belongsTo(Vaga::class, 'vaga_id');
    }
}