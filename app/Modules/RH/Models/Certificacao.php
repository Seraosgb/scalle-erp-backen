<?php

namespace App\Modules\RH\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Certificacao extends Model
{
    protected $table = 'rh_certificacoes';

    protected $fillable = [
        'empresa_id',
        'colaborador_id',
        'nome_certificacao',
        'numero_registro',
        'data_emissao',
        'data_validade',
        'orgao_emissor',
        'status',
        'observacoes',
    ];

    public function colaborador(): BelongsTo
    {
        return $this->belongsTo(Colaborador::class, 'colaborador_id');
    }

    public function getStatusCalculadoAttribute(): string
    {
        if (now()->gt($this->data_validade)) {
            return 'VENCIDO';
        }
        if (now()->diffInDays($this->data_validade, false) <= 30) {
            return 'PRESTES_A_VENCER';
        }
        return 'VALIDO';
    }
}