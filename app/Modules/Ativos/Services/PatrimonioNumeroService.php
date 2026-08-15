<?php

namespace App\Modules\Ativos\Services;

use App\Modules\Ativos\Models\Ativo;

class PatrimonioNumeroService
{
    public static function gerarProximoNumero(int $empresaId): string
    {
        $anoAtual = date('Y');
        $prefixo = "PAT-{$anoAtual}-";

        $ultimo = Ativo::where('empresa_id', $empresaId)
            ->where('codigo_patrimonio', 'like', "{$prefixo}%")
            ->orderBy('id', 'desc')
            ->first();

        if (! $ultimo) {
            return "{$prefixo}000001";
        }

        $partes = explode('-', $ultimo->codigo_patrimonio);
        $sequencial = (int) end($partes);
        $proximo = str_pad($sequencial + 1, 6, '0', STR_PAD_LEFT);

        return "{$prefixo}{$proximo}";
    }
}