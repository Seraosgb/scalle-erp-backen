<?php

namespace App\Modules\RH\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BancoHoras extends Model
{
    protected $table = 'rh_banco_horas';

    protected $fillable = [
        'empresa_id',
        'colaborador_id',
        'ponto_id',
        'data_lancamento',
        'tipo',
        'horas',
        'motivo',
    ];

    public function colaborador(): BelongsTo
    {
        return $this->belongsTo(Colaborador::class, 'colaborador_id');
    }
}<?php

namespace App\Modules\RH\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BancoHoras extends Model
{
    protected $table = 'rh_banco_horas';

    protected $fillable = [
        'empresa_id',
        'colaborador_id',
        'ponto_id',
        'data_lancamento',
        'tipo',
        'horas',
        'motivo',
    ];

    public function colaborador(): BelongsTo
    {
        return $this->belongsTo(Colaborador::class, 'colaborador_id');
    }
}