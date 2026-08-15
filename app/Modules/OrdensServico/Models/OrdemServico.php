<?php

namespace App\Modules\OrdensServico\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Modules\Pessoas\Models\Pessoa;
use App\Models\User;
use \App\Traits\BelongsToTenant;

class OrdemServico extends Model
{
    protected $table = 'os_ordens_servico';

    protected $fillable = [
        'empresa_id',
        'cliente_id',
        'tecnico_id',
        'numero_os',
        'status',
        'data_abertura',
        'previsao_entrega',
        'data_conclusao',
        'defeito_relatado',
        'laudo_tecnico',
        'observacoes_internas',
        'termos_garantia',
        'valor_servicos',
        'valor_produtos',
        'valor_desconto',
        'valor_total',
    ];

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Pessoa::class, 'cliente_id');
    }

    public function tecnico(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tecnico_id');
    }

    public function itens(): HasMany
    {
        return $this->hasMany(OrdemServicoItem::class, 'ordem_servico_id');
    }
}