<?php

namespace App\Modules\Compras\Services;

use App\Modules\Compras\Models\Compra;
use App\Modules\Compras\Models\CompraItem;
use App\Modules\Pessoas\Models\Pessoa;
use App\Modules\Produtos\Models\Item;
use App\Modules\Produtos\Services\ItemService;
use App\Modules\Financeiro\Services\LancamentoFinanceiroService;
use Illuminate\Support\Facades\DB;
use SimpleXMLElement;

class NfeXmlImportService
{
    public function __construct(
        private ItemService $itemService,
        private LancamentoFinanceiroService $financeiroService
    ) {}

    public function importarXml(int $empresaId, string $conteudoXml): Compra
    {
        return DB::transaction(function () use ($empresaId, $conteudoXml) {
            $xml = new SimpleXMLElement($conteudoXml);

            // Suporte para tag raiz com ou sem envelope NFe
            $infNFe = isset($xml->NFe->infNFe) ? $xml->NFe->infNFe : (isset($xml->infNFe) ? $xml->infNFe : null);

            if (!$infNFe) {
                throw new \Exception('O arquivo enviado não possui uma estrutura válida de NF-e.');
            }

            // 1. Processar Fornecedor (<emit>)
            $emit = $infNFe->emit;
            $cnpjCpf = (string) ($emit->CNPJ ?? $emit->CPF);
            $razaoSocial = (string) $emit->xNome;
            $nomeFantasia = (string) ($emit->xFant ?? $razaoSocial);

            $fornecedor = Pessoa::firstOrCreate(
                ['empresa_id' => $empresaId, 'cpf_cnpj' => $cnpjCpf],
                [
                    'tipo_pessoa' => strlen($cnpjCpf) > 11 ? 'J' : 'F',
                    'nome_razao' => $razaoSocial,
                    'nome_fantasia' => $nomeFantasia,
                    'is_cliente' => 0,
                    'is_fornecedor' => 1,
                    'ativo' => 1,
                ]
            );

            // 2. Dados da Nota (<ide> e <total>)
            $numeroNota = (string) $infNFe->ide->nNF;
            $valorTotalNota = (float) $infNFe->total->ICMSTot->vNF;

            $compra = Compra::create([
                'empresa_id' => $empresaId,
                'fornecedor_id' => $fornecedor->id,
                'numero_nota' => $numeroNota,
                'status' => 'RECEBIDO',
                'data_compra' => now(),
                'valor_total' => $valorTotalNota,
                'observacoes' => "Importação automática via XML NF-e nº {$numeroNota}",
            ]);

            // 3. Processar Itens (<det>)
            foreach ($infNFe->det as $det) {
                $prod = $det->prod;
                $sku = (string) $prod->cProd;
                $nomeItem = (string) $prod->xNome;
                $ncm = (string) ($prod->NCM ?? null);
                $cfop = (string) ($prod->CFOP ?? null);
                $qtd = (float) $prod->qCom;
                $valorUnitario = (float) $prod->vUnCom;
                $subtotal = (float) $prod->vProd;

                // Localiza ou cadastra o item
                $item = Item::where('empresa_id', $empresaId)
                    ->where(function ($query) use ($sku, $nomeItem) {
                        $query->where('codigo_sku', $sku)->orWhere('nome', $nomeItem);
                    })
                    ->first();

                if (!$item) {
                    $item = Item::create([
                        'empresa_id' => $empresaId,
                        'tipo' => 'P',
                        'codigo_sku' => $sku,
                        'nome' => $nomeItem,
                        'ncm' => $ncm,
                        'cfop' => $cfop,
                        'preco_custo' => $valorUnitario,
                        'preco_venda' => $valorUnitario * 1.5, // Margem padrão de 50%
                        'estoque_atual' => 0.00,
                        'ativo' => 1,
                    ]);
                } else {
                    $item->update(['preco_custo' => $valorUnitario]);
                }

                CompraItem::create([
                    'compra_id' => $compra->id,
                    'item_id' => $item->id,
                    'quantidade' => $qtd,
                    'valor_unitario' => $valorUnitario,
                    'valor_subtotal' => $subtotal,
                ]);

                // Incrementa estoque físico
                $this->itemService->movimentarEstoque(
                    itemId: $item->id,
                    empresaId: $empresaId,
                    quantidade: $qtd,
                    operacao: 'SOMAR'
                );
            }

            // 4. Gera o Contas a Pagar
            $this->financeiroService->gerarDespesaOrigemCompra($compra);

            return $compra->load(['fornecedor', 'itens.item']);
        });
    }
}