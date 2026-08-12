<?php

namespace App\Modules\Vendas\Services;

use App\Modules\Vendas\Models\Venda;
use App\Modules\Vendas\Models\VendaItem;
use App\Modules\Produtos\Models\Item;
use App\Modules\Produtos\Services\ItemService;
use App\Modules\Financeiro\Services\LancamentoFinanceiroService;
use Illuminate\Support\Facades\DB;

class VendaService
{
    public function __construct(
        private ItemService $itemService,
        private LancamentoFinanceiroService $financeiroService
    ) {}

    public function registrarVenda(int $empresaId, int $vendedorId, array $data): Venda
    {
        return DB::transaction(function () use ($empresaId, $vendedorId, $data) {
            $numeroVenda = VendaNumeroService::gerarProximoNumero($empresaId);

            $venda = Venda::create([
                'empresa_id' => $empresaId,
                'cliente_id' => $data['cliente_id'],
                'vendedor_id' => $vendedorId,
                'numero_venda' => $numeroVenda,
                'status' => 'CONCLUIDO',
                'data_venda' => now(),
                'valor_desconto' => (float) ($data['valor_desconto'] ?? 0),
                'observacoes' => $data['observacoes'] ?? null,
                'valor_subtotal' => 0.00,
                'valor_total' => 0.00,
            ]);

            $subtotalVenda = 0.00;

            foreach ($data['itens'] as $itemVenda) {
                $itemCadastrado = Item::where('id', $itemVenda['item_id'])
                    ->where('empresa_id', $empresaId)
                    ->firstOrFail();

                $quantidade = (float) $itemVenda['quantidade'];
                $valorUnitario = (float) ($itemVenda['valor_unitario'] ?? $itemCadastrado->preco_venda);
                $subtotal = $quantidade * $valorUnitario;
                $subtotalVenda += $subtotal;

                VendaItem::create([
                    'venda_id' => $venda->id,
                    'item_id' => $itemCadastrado->id,
                    'descricao_item' => $itemCadastrado->nome,
                    'quantidade' => $quantidade,
                    'valor_unitario' => $valorUnitario,
                    'valor_subtotal' => $subtotal,
                ]);

                // 📦 GATILHO 1: Subtrai o estoque das peças vendidas
                if ($itemCadastrado->tipo === 'P') {
                    $this->itemService->movimentarEstoque(
                        itemId: $itemCadastrado->id,
                        empresaId: $empresaId,
                        quantidade: $quantidade,
                        operacao: 'SUBTRAIR'
                    );
                }
            }

            $valorTotal = max(0, $subtotalVenda - $venda->valor_desconto);

            $venda->update([
                'valor_subtotal' => $subtotalVenda,
                'valor_total' => $valorTotal,
            ]);

            // 💰 GATILHO 2: Gera a Receita no Contas a Receber
            $this->financeiroService->gerarReceitaOrigemVenda($venda);

            return $venda->load(['cliente', 'vendedor', 'itens']);
        });
    }

    public function listarVendas(int $empresaId)
    {
        return Venda::with(['cliente', 'vendedor', 'itens'])
            ->where('empresa_id', $empresaId)
            ->orderBy('id', 'desc')
            ->paginate(15);
    }
}