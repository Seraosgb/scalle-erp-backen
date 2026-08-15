<?php

namespace App\Modules\Financeiro\Services;

use App\Modules\Financeiro\Models\LancamentoFinanceiro;
use App\Modules\Compras\Models\Compra;
use App\Modules\Vendas\Models\Venda;
use App\Modules\RH\Models\Holerite;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ExportacaoContabilService
{
    public function exportarMovimentacaoPeriodo(int $empresaId, string $dataInicio, string $dataFim, string $formato = 'JSON'): array|string
    {
        $lancamentos = LancamentoFinanceiro::with('pessoa')
            ->where('empresa_id', $empresaId)
            ->whereBetween('data_vencimento', [$dataInicio, $dataFim])
            ->orderBy('data_vencimento')
            ->get();

        $compras = Compra::with('fornecedor')
            ->where('empresa_id', $empresaId)
            ->whereBetween('created_at', [
                Carbon::parse($dataInicio)->startOfDay(),
                Carbon::parse($dataFim)->endOfDay()
            ])
            ->get();

        $vendas = Venda::with('cliente')
            ->where('empresa_id', $empresaId)
            ->whereBetween('created_at', [
                Carbon::parse($dataInicio)->startOfDay(),
                Carbon::parse($dataFim)->endOfDay()
            ])
            ->get();

        $folha = Holerite::with('colaborador')
            ->where('empresa_id', $empresaId)
            ->whereBetween('created_at', [
                Carbon::parse($dataInicio)->startOfDay(),
                Carbon::parse($dataFim)->endOfDay()
            ])
            ->get();

        $dadosConsolidados = [
            'cabecalho' => [
                'empresa_id' => $empresaId,
                'periodo_inicio' => $dataInicio,
                'periodo_fim' => $dataFim,
                'gerado_em' => now()->toIso8601String(),
                'versao_layout' => 'SCALLE-SPED-1.0'
            ],
            'financeiro' => $lancamentos->map(fn($l) => [
                'id' => $l->id,
                'tipo' => $l->tipo,
                'descricao' => $l->descricao,
                'documento_contraparte' => $l->pessoa?->cpf_cnpj ?? '00000000000',
                'nome_contraparte' => $l->pessoa?->nome_razao ?? 'DIVERSOS',
                'valor' => (float) $l->valor,
                'data_vencimento' => $l->data_vencimento?->toDateString(),
                'data_pagamento' => $l->data_pagamento?->toDateString(),
                'forma_pagamento' => $l->forma_pagamento ?? 'NAO_INFORMADO',
                'status' => $l->status
            ]),
            'compras' => $compras->map(fn($c) => [
                'id' => $c->id,
                'fornecedor' => $c->fornecedor?->nome_razao ?? 'FORNECEDOR DIVERSO',
                'cnpj' => $c->fornecedor?->cpf_cnpj ?? '00000000000000',
                'valor_total' => (float) $c->valor_total,
                'data_emissao' => $c->created_at->toDateString(),
            ]),
            'vendas' => $vendas->map(fn($v) => [
                'id' => $v->id,
                'cliente' => $v->cliente?->nome_razao ?? 'CONSUMIDOR FINAL',
                'documento' => $v->cliente?->cpf_cnpj ?? '00000000000',
                'valor_total' => (float) $v->valor_total,
                'data_venda' => $v->created_at->toDateString(),
            ]),
            'folha_pagamento' => $folha->map(fn($h) => [
                'id' => $h->id,
                'colaborador' => $h->colaborador?->nome_completo,
                'cpf' => $h->colaborador?->cpf,
                'competencia' => $h->mes_ano_competencia,
                'proventos' => (float) $h->proventos_total,
                'descontos' => (float) $h->descontos_total,
                'liquido' => (float) $h->valor_liquido,
            ])
        ];

        if (strtoupper($formato) === 'CSV') {
            return $this->gerarCsvPlano($dadosConsolidados['financeiro']);
        }

        return $dadosConsolidados;
    }

    private function gerarCsvPlano(Collection $financeiro): string
    {
        $linhas = [];
        $linhas[] = "ID;TIPO;DESCRICAO;DOCUMENTO_CONTRAPARTE;NOME_CONTRAPARTE;VALOR;DATA_VENCIMENTO;DATA_PAGAMENTO;FORMA_PAGAMENTO;STATUS";

        foreach ($financeiro as $item) {
            $linhas[] = implode(';', [
                $item['id'],
                $item['tipo'],
                str_replace(';', ',', $item['descricao']),
                $item['documento_contraparte'],
                str_replace(';', ',', $item['nome_contraparte']),
                number_format($item['valor'], 2, ',', ''),
                $item['data_vencimento'] ?? '',
                $item['data_pagamento'] ?? '',
                $item['forma_pagamento'],
                $item['status']
            ]);
        }

        return implode("\r\n", $linhas);
    }
}