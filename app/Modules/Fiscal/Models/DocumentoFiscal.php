<?php

namespace App\Modules\Fiscal\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Modules\Vendas\Models\Venda;
use App\Modules\OrdensServico\Models\OrdemServico;
use \App\Traits\BelongsToTenant;

class DocumentoFiscal extends Model
{
    protected $table = 'fis_documentos_fiscais';

    protected $fillable = [
        'empresa_id',
        'venda_id',
        'ordem_servico_id',
        'tipo_doc',
        'numero_nota',
        'serie',
        'chave_acesso',
        'protocolo',
        'status',
        'mensagem_sefaz',
        'url_pdf',
        'url_xml',
        'valor_total',
        'data_emissao',
    ];

    public function venda(): BelongsTo
    {
        return $this->belongsTo(Venda::class, 'venda_id');
    }

    public function ordemServico(): BelongsTo
    {
        return $this->belongsTo(OrdemServico::class, 'ordem_servico_id');
    }
}