<?php

namespace App\Modules\Pessoas\DTOs;

class PessoaDTO
{
    public function __construct(
        public readonly int $empresaId,
        public readonly string $nomeRazao,
        public readonly string $cpfCnpj,
        public readonly string $tipoPessoa,
        public readonly ?string $nomeFantasia = null,
        public readonly ?string $email = null,
        public readonly ?string $telefone = null,
        public readonly bool $isCliente = true,
        public readonly bool $isFornecedor = false
    ) {}

    public static function fromRequest(array $data, int $empresaId): self
    {
        return new self(
            empresaId: $empresaId,
            nomeRazao: $data['nome_razao'],
            cpfCnpj: $data['cpf_cnpj'],
            tipoPessoa: $data['tipo_pessoa'] ?? 'F',
            nomeFantasia: $data['nome_fantasia'] ?? null,
            email: $data['email'] ?? null,
            telefone: $data['telefone'] ?? null,
            isCliente: $data['is_cliente'] ?? true,
            isFornecedor: $data['is_fornecedor'] ?? false
        );
    }

    public function toArray(): array
    {
        return [
            'empresa_id' => $this->empresaId,
            'nome_razao' => $this->nomeRazao,
            'cpf_cnpj' => $this->cpfCnpj,
            'tipo_pessoa' => $this->tipoPessoa,
            'nome_fantasia' => $this->nomeFantasia,
            'email' => $this->email,
            'telefone' => $this->telefone,
            'is_cliente' => $this->isCliente,
            'is_fornecedor' => $this->isFornecedor,
        ];
    }
}