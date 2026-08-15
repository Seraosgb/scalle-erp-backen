<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmpresaParametro extends Model
{
    protected $table = 'sis_empresa_parametros';

    protected $fillable = [
        'empresa_id',
        'chave',
        'valor',
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }
}