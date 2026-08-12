<?php

namespace App\Modules\Produtos\Services;

use App\Modules\Produtos\Models\Item;
use App\Modules\Produtos\DTOs\ItemDTO;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ItemService
{
    public function criarItem(ItemDTO $dto): Item
    {
        return Item::create($dto->toArray());
    }

    public function listarItens(int $empresaId, array $filtros = []): LengthAwarePaginator
    {
        $query = Item::with(['categoria', 'unidade'])->where('empresa_id', $empresaId);

        if (!empty($filtros['busca'])) {
            $busca = $filtros['busca'];
            $query->where(function ($q) use ($busca) {
                $q->where('nome', 'like', "%{$busca}%")
                  ->orWhere('codigo_sku', 'like', "%{$busca}%")
                  ->orWhere('codigo_barras', 'like', "%{$busca}%");
            });
        }

        if (!empty($filtros['tipo'])) {
            $query->where('tipo', strtoupper($filtros['tipo']));
        }

        return $query->where('ativo', true)->orderBy('nome')->paginate(15);
    }

    public function buscarPorId(int $id, int $empresaId): ?Item
    {
        return Item::with(['categoria', 'unidade'])
            ->where('id', $id)
            ->where('empresa_id', $empresaId)
            ->first();
    }

    public function atualizarItem(Item $item, ItemDTO $dto): Item
    {
        $item->update($dto->toArray());
        return $item;
    }

    public function inativarItem(Item $item): bool
    {
        return $item->update(['ativo' => false]);
    }

    public function movimentarEstoque(int $itemId, int $empresaId, float $quantidade, string $operacao): void
    {
        $item = Item::where('id', $itemId)
            ->where('empresa_id', $empresaId)
            ->first();

        // Se o item não existir ou for um Serviço ('S'), não movimenta estoque
        if (!$item || $item->tipo === 'S') {
            return;
        }

        if ($operacao === 'SUBTRAIR') {
            $item->decrement('estoque_atual', $quantidade);
        } elseif ($operacao === 'SOMAR') {
            $item->increment('estoque_atual', $quantidade);
        }
    }
}