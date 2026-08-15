<?php

namespace App\Modules\Financeiro\Services;

use App\Modules\Financeiro\Models\LancamentoFinanceiro;
use App\Models\EmpresaParametro;

class PixService
{
    /**
     * Retorna a chave PIX dinâmica da empresa ou recorre ao fallback
     */
    public function obterChavePixEmpresa(int $empresaId): string
    {
        $param = EmpresaParametro::where('empresa_id', $empresaId)
            ->where('chave', 'CHAVE_PIX_PADRAO')
            ->first();

        return ($param && !empty($param->valor)) ? $param->valor : env('PIX_CHAVE_PADRAO', 'suachavepix@email.com');
    }

    /**
     * Gera o código PIX Copia e Cola (Payload BR Code EMV)
     */
    public function gerarPixCopiaECola(LancamentoFinanceiro $lancamento, ?string $chavePix = null): string
    {
        $chave = $chavePix ?? $this->obterChavePixEmpresa($lancamento->empresa_id);
        $valorFormatted = number_format((float) $lancamento->valor, 2, '.', '');
        $nomeRecebedor = mb_substr($lancamento->empresa->nome_fantasia ?? $lancamento->empresa->razao_social ?? 'Scalle ERP', 0, 25);
        $cidade = 'RIO DE JANEIRO';
        $txid = "SCALLE" . str_pad($lancamento->id, 10, '0', STR_PAD_LEFT);

        // Estrutura do Payload EMV
        $payload  = $this->formatElement('00', '01');
        
        $gui = $this->formatElement('00', 'br.gov.bcb.pix');
        $chaveElement = $this->formatElement('01', $chave);
        $payload .= $this->formatElement('26', $gui . $chaveElement);

        $payload .= $this->formatElement('52', '0000');
        $payload .= $this->formatElement('53', '986');
        $payload .= $this->formatElement('54', $valorFormatted);
        $payload .= $this->formatElement('58', 'BR');
        $payload .= $this->formatElement('59', $nomeRecebedor);
        $payload .= $this->formatElement('60', $cidade);
        
        $txidElement = $this->formatElement('05', $txid);
        $payload .= $this->formatElement('62', $txidElement);

        $payload .= '6304';
        $payload .= $this->calcularCRC16($payload);

        return $payload;
    }

    private function formatElement(string $id, string $value): string
    {
        $length = str_pad((string) mb_strlen($value), 2, '0', STR_PAD_LEFT);
        return $id . $length . $value;
    }

    private function calcularCRC16(string $payload): string
    {
        $resultado = 0xFFFF;
        for ($i = 0; $i < strlen($payload); $i++) {
            $resultado ^= (ord($payload[$i]) << 8);
            for ($j = 0; $j < 8; $j++) {
                if (($resultado & 0x8000) !== 0) {
                    $resultado = (($resultado << 1) ^ 0x1021) & 0xFFFF;
                } else {
                    $resultado = ($resultado << 1) & 0xFFFF;
                }
            }
        }
        return strtoupper(str_pad(dechex($resultado), 4, '0', STR_PAD_LEFT));
    }
}