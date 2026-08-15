<?php

namespace App\Modules\Frotas\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\User;
use App\Modules\OrdensServico\Models\OrdemServico;

class Veiculo extends Model
{
    protected $table = 'fro_veiculos';

    protected $fillable = [
        'empresa_id',
        'motorista_padrao_id',
        'placa',
        'modelo',
        'marca',
        'ano_fabricacao',
        'combustivel_tipo',
        'km_atual',
        'status',
        'ativo',
    ];

    public function motoristaPadrao(): BelongsTo
    {
        return $this->belongsTo(User::class, 'motorista_padrao_id');
    }

    public function abastecimentos(): HasMany
    {
        return $this->hasMany(Abastecimento::class, 'veiculo_id');
    }

    public function manutencoes(): HasMany
    {
        return $this->hasMany(OrdemServico::class, 'veiculo_id');
    }
}