<?php

namespace App\Modules\Fiscal\Contracts;

use App\Modules\Vendas\Models\Venda;
use App\Modules\OrdensServico\Models\OrdemServico;

interface FiscalDriverInterface
{
    /**
     * Transmite a NFe referente a uma Venda Direta
     */
    public function emitirNFeVenda(Venda $venda): array;

    /**
     * Transmite a NFS-e referente a uma Ordem de Serviço
     */
    public function emitirNFSeOS(OrdemServico $os): array;

    public function emitirCTe(array $dadosTransporte): array;
    public function emitirMDFe(array $dadosManifesto): array;
}