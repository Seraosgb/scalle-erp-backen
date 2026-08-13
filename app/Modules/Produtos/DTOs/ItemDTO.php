<?php

namespace App\Modules\Produtos\DTOs;

use App\Modules\Produtos\Models\Item;

class ItemDTO
{
    public function __construct(
        public readonly int $empresaId,
        public readonly string $nome,
        public readonly string $tipo,
        public readonly float $precoVenda,
        public readonly float $precoCusto = 0.00,
        public readonly ?int $categoriaId = null,
        public readonly ?int $unidadeId = null,
        public readonly ?string $codigoSku = null,
        public readonly ?string $codigoBarras = null,
        public readonly ?string $descricao = null,
        public readonly ?string $ncm = null,
        public readonly ?string $cest = null,
        public readonly ?string $cfop = null,
        public readonly int $origemMercadoria = 0,
        public readonly float $estoqueAtual = 0.00
    ) {}

    public static function fromRequest(array $data, int $empresaId): self
    {
        return new self(
            empresaId: $empresaId,
            nome: $data['nome'],
            tipo: strtoupper($data['tipo'] ?? 'P'),
            precoVenda: (float) ($data['preco_venda'] ?? 0),
            precoCusto: (float) ($data['preco_custo'] ?? 0),
            categoriaId: isset($data['categoria_id']) ? (int) $data['categoria_id'] : null,
            unidadeId: isset($data['unidade_id']) ? (int) $data['unidade_id'] : null,
            codigoSku: $data['codigo_sku'] ?? null,
            codigoBarras: $data['codigo_barras'] ?? null,
            descricao: $data['descricao'] ?? null,
            ncm: $data['ncm'] ?? null,
            cest: $data['cest'] ?? null,
            cfop: $data['cfop'] ?? null,
            origemMercadoria: isset($data['origem_mercadoria']) ? (int) $data['origem_mercadoria'] : 0,
            estoqueAtual: (float) ($data['estoque_atual'] ?? 0)
        );
    }

    public static function fromUpdate(array $data, Item $itemExistente, int $empresaId): self
    {
        return new self(
            empresaId: $empresaId,
            nome: $data['nome'] ?? $itemExistente->nome,
            tipo: strtoupper($data['tipo'] ?? $itemExistente->tipo),
            precoVenda: isset($data['preco_venda']) ? (float) $data['preco_venda'] : (float) $itemExistente->preco_venda,
            precoCusto: isset($data['preco_custo']) ? (float) $data['preco_custo'] : (float) $itemExistente->preco_custo,
            categoriaId: isset($data['categoria_id']) ? (int) $data['categoria_id'] : $itemExistente->categoria_id,
            unidadeId: isset($data['unidade_id']) ? (int) $data['unidade_id'] : $itemExistente->unidade_id,
            codigoSku: $data['codigo_sku'] ?? $itemExistente->codigo_sku,
            codigoBarras: $data['codigo_barras'] ?? $itemExistente->codigo_barras,
            descricao: $data['descricao'] ?? $itemExistente->descricao,
            ncm: $data['ncm'] ?? $itemExistente->ncm,
            cest: $data['cest'] ?? $itemExistente->cest,
            cfop: $data['cfop'] ?? $itemExistente->cfop,
            origemMercadoria: isset($data['origem_mercadoria']) ? (int) $data['origem_mercadoria'] : (int) $itemExistente->origem_mercadoria,
            estoqueAtual: (float) $itemExistente->estoque_atual
        );
    }

    public function toArray(): array
    {
        return [
            'empresa_id' => $this->empresaId,
            'categoria_id' => $this->categoriaId,
            'unidade_id' => $this->unidadeId,
            'tipo' => $this->tipo,
            'codigo_sku' => $this->codigoSku,
            'codigo_barras' => $this->codigoBarras,
            'nome' => $this->nome,
            'descricao' => $this->descricao,
            'ncm' => $this->ncm,
            'cest' => $this->cest,
            'cfop' => $this->cfop,
            'origem_mercadoria' => $this->origemMercadoria,
            'preco_custo' => $this->precoCusto,
            'preco_venda' => $this->precoVenda,
            'estoque_atual' => $this->tipo === 'S' ? 0.00 : $this->estoqueAtual,
        ];
    }
}