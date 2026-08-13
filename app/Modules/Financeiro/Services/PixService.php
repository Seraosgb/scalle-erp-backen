<?php

namespace App\Modules\Financeiro\Services;

use App\Modules\Financeiro\Models\LancamentoFinanceiro;

class PixService
{
    /**
     * Gera o código PIX Copia e Cola (Payload BR Code EMV)
     */
    public function gerarPixCopiaECola(LancamentoFinanceiro $lancamento, string $chavePix): string
    {
        $valorFormatted = number_format((float) $lancamento->valor, 2, '.', '');
        $nomeRecebedor = mb_substr($lancamento->empresa->nome_fantasia ?? $lancamento->empresa->razao_social ?? 'Scalle ERP', 0, 25);
        $cidade = 'RIO DE JANEIRO'; // Ajustável por configuração
        $txid = "SCALLE" . str_pad($lancamento->id, 10, '0', STR_PAD_LEFT);

        // Estrutura do Payload EMV
        $payload  = $this->formatElement('00', '01'); // Payload Format Indicator
        
        // Merchant Account Information
        $gui = $this->formatElement('00', 'br.gov.bcb.pix');
        $chave = $this->formatElement('01', $chavePix);
        $payload .= $this->formatElement('26', $gui . $chave);

        $payload .= $this->formatElement('52', '0000'); // Merchant Category Code
        $payload .= $this->formatElement('53', '986');  // Transaction Currency (BRL)
        $payload .= $this->formatElement('54', $valorFormatted); // Transaction Amount
        $payload .= $this->formatElement('58', 'BR');   // Country Code
        $payload .= $this->formatElement('59', $nomeRecebedor); // Merchant Name
        $payload .= $this->formatElement('60', $cidade); // Merchant City
        
        // Additional Data Field (TXID)
        $txidElement = $this->formatElement('05', $txid);
        $payload .= $this->formatElement('62', $txidElement);

        // CRC16 Checksum Placeholder
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