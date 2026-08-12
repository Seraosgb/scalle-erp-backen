<?php

namespace App\Modules\Compras\Services;

use App\Modules\Compras\Models\Compra;
use App\Modules\Compras\Models\CompraItem;
use App\Modules\Produtos\Models\Item;
use App\Modules\Produtos\Services\ItemService;
use App\Modules\Financeiro\Services\LancamentoFinanceiroService;
use Illuminate\Support\Facades\DB;

class CompraService
{
    public function __construct(
        private ItemService $itemService,
        private LancamentoFinanceiroService $financeiroService
    ) {}

    public function registrarCompra(int $empresaId, array $data): Compra
    {
        return DB::transaction(function () use ($empresaId, $data) {
            $compra = Compra::create([
                'empresa_id' => $empresaId,
                'fornecedor_id' => $data['fornecedor_id'],
                'numero_nota' => $data['numero_nota'] ?? null,
                'status' => 'RECEBIDO',
                'data_compra' => now(),
                'observacoes' => $data['observacoes'] ?? null,
                'valor_total' => 0.00,
            ]);

            $totalCompra = 0.00;

            foreach ($data['itens'] as $itemCompra) {
                $itemCadastrado = Item::where('id', $itemCompra['item_id'])
                    ->where('empresa_id', $empresaId)
                    ->firstOrFail();

                $quantidade = (float) $itemCompra['quantidade'];
                $valorUnitario = (float) $itemCompra['valor_unitario'];
                $subtotal = $quantidade * $valorUnitario;
                $totalCompra += $subtotal;

                CompraItem::create([
                    'compra_id' => $compra->id,
                    'item_id' => $itemCadastrado->id,
                    'quantidade' => $quantidade,
                    'valor_unitario' => $valorUnitario,
                    'valor_subtotal' => $subtotal,
                ]);

                // 📦 GATILHO 1: Incrementa o estoque e atualiza o preço de custo da peça
                if ($itemCadastrado->tipo === 'P') {
                    $this->itemService->movimentarEstoque(
                        itemId: $itemCadastrado->id,
                        empresaId: $empresaId,
                        quantidade: $quantidade,
                        operacao: 'SOMAR'
                    );

                    $itemCadastrado->update(['preco_custo' => $valorUnitario]);
                }
            }

            $compra->update(['valor_total' => $totalCompra]);

            // 💰 GATILHO 2: Gera o Contas a Pagar no Financeiro (DESPESA)
            $this->financeiroService->gerarDespesaOrigemCompra($compra);

            return $compra->load(['fornecedor', 'itens']);
        });
    }

    public function listarCompras(int $empresaId)
    {
        return Compra::with(['fornecedor', 'itens'])
            ->where('empresa_id', $empresaId)
            ->orderBy('id', 'desc')
            ->paginate(15);
    }
}