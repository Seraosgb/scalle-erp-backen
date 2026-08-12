<?php

namespace App\Modules\OrdensServico\Services;

use App\Modules\OrdensServico\Models\OrdemServico;

class OsNumeroService
{
    public static function gerarProximoNumero(int $empresaId): string
    {
        $anoAtual = date('Y');
        $prefixo = "OS-{$anoAtual}-";

        $ultimaOs = OrdemServico::where('empresa_id', $empresaId)
            ->where('numero_os', 'like', "{$prefixo}%")
            ->orderBy('id', 'desc')
            ->first();

        if (! $ultimaOs) {
            return "{$prefixo}000001"; // 6 dígitos
        }

        $partes = explode('-', $ultimaOs->numero_os);
        $sequencial = (int) end($partes);
        $proximoSequencial = str_pad($sequencial + 1, 6, '0', STR_PAD_LEFT);

        return "{$prefixo}{$proximoSequencial}";
    }
}