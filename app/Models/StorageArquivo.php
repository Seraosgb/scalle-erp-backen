<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StorageArquivo extends Model
{
    use BelongsToTenant;

    protected $table = 'sis_storage_arquivos';

    protected $fillable = [
        'empresa_id',
        'user_id',
        'modulo',
        'nome_original',
        'caminho_storage',
        'tamanho_bytes',
        'mime_type',
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }
}