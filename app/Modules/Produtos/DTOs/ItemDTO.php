<?php

namespace App\Modules\Produtos\DTOs;

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
            estoqueAtual: (float) ($data['estoque_atual'] ?? 0)
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
            'preco_custo' => $this->precoCusto,
            'preco_venda' => $this->precoVenda,
            'estoque_atual' => $this->tipo === 'S' ? 0.00 : $this->estoqueAtual,
        ];
    }
}