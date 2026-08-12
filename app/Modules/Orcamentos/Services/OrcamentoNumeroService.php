<?php

namespace App\Modules\Orcamentos\Services;

use App\Modules\Orcamentos\Models\Orcamento;

class OrcamentoNumeroService
{
    public static function gerarProximoNumero(int $empresaId): string
    {
        $anoAtual = date('Y');
        $prefixo = "ORC-{$anoAtual}-";

        $ultimoOrcamento = Orcamento::where('empresa_id', $empresaId)
            ->where('numero_orcamento', 'like', "{$prefixo}%")
            ->orderBy('id', 'desc')
            ->first();

        if (! $ultimoOrcamento) {
            return "{$prefixo}000001";
        }

        $partes = explode('-', $ultimoOrcamento->numero_orcamento);
        $sequencial = (int) end($partes);
        $proximoSequencial = str_pad($sequencial + 1, 6, '0', STR_PAD_LEFT);

        return "{$prefixo}{$proximoSequencial}";
    }
}