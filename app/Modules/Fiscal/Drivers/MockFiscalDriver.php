<?php

namespace App\Modules\Fiscal\Drivers;

use App\Modules\Fiscal\Contracts\FiscalDriverInterface;
use App\Modules\Vendas\Models\Venda;
use App\Modules\OrdensServico\Models\OrdemServico;

class MockFiscalDriver implements FiscalDriverInterface
{
    public function emitirNFeVenda(Venda $venda): array
    {
        $uf = $venda->empresa->uf ?? '33';
        $anoMes = date('ym');
        $cnpj = str_pad(preg_replace('/\D/', '', $venda->empresa->cpf_cnpj ?? '12345678000199'), 14, '0', STR_PAD_LEFT);
        $mod = '55';
        $serie = '001';
        $numero = str_pad((string) rand(1, 999999), 9, '0', STR_PAD_LEFT);
        $codigoAleatorio = str_pad((string) rand(1, 99999999), 8, '0', STR_PAD_LEFT);

        $chaveAcesso = "{$uf}{$anoMes}{$cnpj}{$mod}{$serie}{$numero}1{$codigoAleatorio}1";

        return [
            'status' => 'AUTORIZADO',
            'numero_nota' => ltrim($numero, '0'),
            'serie' => '1',
            'chave_acesso' => $chaveAcesso,
            'protocolo' => '13326' . rand(100000000, 999999999),
            'mensagem_sefaz' => 'Autorizado o uso da NF-e',
            'url_pdf' => "https://scalle.btec.top/docs/danfe_mock_{$venda->id}.pdf",
            'url_xml' => "https://scalle.btec.top/docs/xml_mock_{$venda->id}.xml",
        ];
    }

    public function emitirNFSeOS(OrdemServico $os): array
    {
        $numero = (string) rand(100, 99999);

        return [
            'status' => 'AUTORIZADO',
            'numero_nota' => $numero,
            'serie' => '1',
            'chave_acesso' => null,
            'protocolo' => 'NFS' . rand(100000, 999999),
            'mensagem_sefaz' => 'NFS-e emitida com sucesso no Município',
            'url_pdf' => "https://scalle.btec.top/docs/nfse_mock_{$os->id}.pdf",
            'url_xml' => "https://scalle.btec.top/docs/nfse_xml_mock_{$os->id}.xml",
        ];
    }
}