<?php

namespace App\Modules\OrdensServico\DTOs;

class OrdemServicoDTO
{
    public function __construct(
        public readonly int $empresaId,
        public readonly int $clienteId,
        public readonly ?int $tecnicoId = null,
        public readonly ?string $status = 'ABERTO',
        public readonly ?string $previsaoEntrega = null,
        public readonly ?string $defeitoRelatado = null,
        public readonly ?string $laudoTecnico = null,
        public readonly ?string $observacoesInternas = null,
        public readonly ?string $termosGarantia = null,
        public readonly float $valorDesconto = 0.00,
        public readonly array $itens = [] // Array de itens da OS
    ) {}

    public static function fromRequest(array $data, int $empresaId): self
    {
        return new self(
            empresaId: $empresaId,
            clienteId: (int) $data['cliente_id'],
            tecnicoId: isset($data['tecnico_id']) ? (int) $data['tecnico_id'] : null,
            status: strtoupper($data['status'] ?? 'ABERTO'),
            previsaoEntrega: $data['previsao_entrega'] ?? null,
            defeitoRelatado: $data['defeito_relatado'] ?? null,
            laudoTecnico: $data['laudo_tecnico'] ?? null,
            observacoesInternas: $data['observacoes_internas'] ?? null,
            termosGarantia: $data['termos_garantia'] ?? null,
            valorDesconto: (float) ($data['valor_desconto'] ?? 0),
            itens: $data['itens'] ?? []
        );
    }
}