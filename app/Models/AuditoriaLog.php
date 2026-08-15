<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditoriaLog extends Model
{
    protected $table = 'sis_auditoria_logs';

    protected $fillable = [
        'empresa_id',
        'user_id',
        'acao',
        'tabela',
        'registro_id',
        'dados_antigos',
        'dados_novos',
        'ip_address',
    ];

    protected $casts = [
        'dados_antigos' => 'array',
        'dados_novos' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}