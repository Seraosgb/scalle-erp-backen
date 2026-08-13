<?php

namespace App\Modules\Fiscal\Services;

use App\Modules\Fiscal\Contracts\FiscalDriverInterface;
use App\Modules\Fiscal\Models\DocumentoFiscal;
use App\Modules\Vendas\Models\Venda;
use App\Modules\OrdensServico\Models\OrdemServico;
use Illuminate\Support\Facades\DB;

class FiscalService
{
    public function __construct(private FiscalDriverInterface $fiscalDriver) {}

    public function emitirNFeVenda(int $vendaId, int $empresaId): DocumentoFiscal
    {
        return DB::transaction(function () use ($vendaId, $empresaId) {
            $venda = Venda::with(['cliente', 'itens.item'])
                ->where('id', $vendaId)
                ->where('empresa_id', $empresaId)
                ->firstOrFail();

            $docExistente = DocumentoFiscal::where('venda_id', $venda->id)
                ->where('status', 'AUTORIZADO')
                ->first();

            if ($docExistente) {
                throw new \Exception("Esta venda já possui a NFe nº {$docExistente->numero_nota} autorizada.");
            }

            // Dispara a emissão via Driver
            $retorno = $this->fiscalDriver->emitirNFeVenda($venda);

            return DocumentoFiscal::create([
                'empresa_id' => $empresaId,
                'venda_id' => $venda->id,
                'tipo_doc' => 'NFE',
                'numero_nota' => $retorno['numero_nota'],
                'serie' => $retorno['serie'],
                'chave_acesso' => $retorno['chave_acesso'],
                'protocolo' => $retorno['protocolo'],
                'status' => $retorno['status'],
                'mensagem_sefaz' => $retorno['mensagem_sefaz'],
                'url_pdf' => $retorno['url_pdf'],
                'url_xml' => $retorno['url_xml'],
                'valor_total' => $venda->valor_total,
                'data_emissao' => now(),
            ]);
        });
    }

    public function emitirNFSeOS(int $osId, int $empresaId): DocumentoFiscal
    {
        return DB::transaction(function () use ($osId, $empresaId) {
            $os = OrdemServico::with(['cliente', 'itens.item'])
                ->where('id', $osId)
                ->where('empresa_id', $empresaId)
                ->firstOrFail();

            $docExistente = DocumentoFiscal::where('ordem_servico_id', $os->id)
                ->where('status', 'AUTORIZADO')
                ->first();

            if ($docExistente) {
                throw new \Exception("Esta OS já possui a NFS-e nº {$docExistente->numero_nota} autorizada.");
            }

            // Dispara a emissão via Driver
            $retorno = $this->fiscalDriver->emitirNFSeOS($os);

            return DocumentoFiscal::create([
                'empresa_id' => $empresaId,
                'ordem_servico_id' => $os->id,
                'tipo_doc' => 'NFSE',
                'numero_nota' => $retorno['numero_nota'],
                'serie' => $retorno['serie'],
                'protocolo' => $retorno['protocolo'],
                'status' => $retorno['status'],
                'mensagem_sefaz' => $retorno['mensagem_sefaz'],
                'url_pdf' => $retorno['url_pdf'],
                'url_xml' => $retorno['url_xml'],
                'valor_total' => $os->valor_servicos,
                'data_emissao' => now(),
            ]);
        });
    }
}