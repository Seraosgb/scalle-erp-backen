<?php

namespace App\Modules\Vendas\Services;

use App\Modules\Vendas\Models\Venda;

class VendaNumeroService
{
    public static function gerarProximoNumero(int $empresaId): string
    {
        $anoAtual = date('Y');
        $prefixo = "VEN-{$anoAtual}-";

        $ultimaVenda = Venda::where('empresa_id', $empresaId)
            ->where('numero_venda', 'like', "{$prefixo}%")
            ->orderBy('id', 'desc')
            ->first();

        if (! $ultimaVenda) {
            return "{$prefixo}000001";
        }

        $partes = explode('-', $ultimaVenda->numero_venda);
        $sequencial = (int) end($partes);
        $proximoSequencial = str_pad($sequencial + 1, 6, '0', STR_PAD_LEFT);

        return "{$prefixo}{$proximoSequencial}";
    }
}