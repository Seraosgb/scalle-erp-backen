<?php

namespace App\Modules\PCP\Services;

use App\Modules\PCP\Models\OrdemProducao;

class OpNumeroService
{
    public static function gerarProximoNumero(int $empresaId): string
    {
        $anoAtual = date('Y');
        $prefixo = "OP-{$anoAtual}-";

        $ultimaOp = OrdemProducao::where('empresa_id', $empresaId)
            ->where('numero_op', 'like', "{$prefixo}%")
            ->orderBy('id', 'desc')
            ->first();

        if (! $ultimaOp) {
            return "{$prefixo}000001";
        }

        $partes = explode('-', $ultimaOp->numero_op);
        $sequencial = (int) end($partes);
        $proximoSequencial = str_pad($sequencial + 1, 6, '0', STR_PAD_LEFT);

        return "{$prefixo}{$proximoSequencial}";
    }
}