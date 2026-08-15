<?php

namespace App\Modules\WMS\Services;

use App\Modules\WMS\Models\Transferencia;

class TransferenciaNumeroService
{
    public static function gerarProximoNumero(int $empresaId): string
    {
        $anoAtual = date('Y');
        $prefixo = "TRF-{$anoAtual}-";

        $ultima = Transferencia::where('empresa_id', $empresaId)
            ->where('numero_transferencia', 'like', "{$prefixo}%")
            ->orderBy('id', 'desc')
            ->first();

        if (! $ultima) {
            return "{$prefixo}000001";
        }

        $partes = explode('-', $ultima->numero_transferencia);
        $sequencial = (int) end($partes);
        $proximo = str_pad($sequencial + 1, 6, '0', STR_PAD_LEFT);

        return "{$prefixo}{$proximo}";
    }
}