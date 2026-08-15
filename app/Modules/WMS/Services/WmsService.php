<?php

namespace App\Modules\WMS\Services;

use App\Modules\WMS\Models\Deposito;
use App\Modules\WMS\Models\EstoqueDeposito;
use App\Modules\WMS\Models\Transferencia;
use App\Modules\WMS\Models\TransferenciaItem;
use App\Modules\Produtos\Models\Item;
use Illuminate\Support\Facades\DB;

class WmsService
{
    /**
     * Movimenta estoque em um depósito específico
     */
    public function movimentarEstoqueDeposito(int $empresaId, int $depositoId, int $itemId, float $quantidade, string $operacao): void
    {
        $item = Item::where('id', $itemId)->where('empresa_id', $empresaId)->first();
        if (! $item || $item->tipo === 'S') {
            return;
        }

        $registro = EstoqueDeposito::firstOrCreate(
            ['empresa_id' => $empresaId, 'deposito_id' => $depositoId, 'item_id' => $itemId],
            ['quantidade' => 0.0000]
        );

        if ($operacao === 'SUBTRAIR') {
            $registro->decrement('quantidade', $quantidade);
            $item->decrement('estoque_atual', $quantidade);
        } elseif ($operacao === 'SOMAR') {
            $registro->increment('quantidade', $quantidade);
            $item->increment('estoque_atual', $quantidade);
        }
    }

    /**
     * Executa Transferência Interna (Suporta modo DIRETO ou EM_TRANSITO)
     */
    public function criarTransferencia(int $empresaId, int $solicitanteId, array $data): Transferencia
    {
        return DB::transaction(function () use ($empresaId, $solicitanteId, $data) {
            $origemId = $data['deposito_origem_id'];
            $destinoId = $data['deposito_destino_id'];

            if ($origemId === $destinoId) {
                throw new \Exception('O depósito de origem e destino não podem ser iguais.');
            }

            $numero = TransferenciaNumeroService::gerarProximoNumero($empresaId);
            $modo = strtoupper($data['modo'] ?? 'DIRETO'); // DIRETO ou TRANSITO

            $transferencia = Transferencia::create([
                'empresa_id' => $empresaId,
                'deposito_origem_id' => $origemId,
                'deposito_destino_id' => $destinoId,
                'solicitante_id' => $solicitanteId,
                'numero_transferencia' => $numero,
                'status' => $modo === 'DIRETO' ? 'CONCLUIDA' : 'EM_TRANSITO',
                'observacoes' => $data['observacoes'] ?? null,
                'data_envio' => now(),
                'data_recebimento' => $modo === 'DIRETO' ? now() : null,
            ]);

            foreach ($data['itens'] as $itemTrf) {
                $itemId = $itemTrf['item_id'];
                $qtd = (float) $itemTrf['quantidade'];

                TransferenciaItem::create([
                    'transferencia_id' => $transferencia->id,
                    'item_id' => $itemId,
                    'quantidade' => $qtd,
                ]);

                // 📦 1. Sempre subtrai da origem no envio
                $estoqueOrigem = EstoqueDeposito::firstOrCreate(
                    ['empresa_id' => $empresaId, 'deposito_id' => $origemId, 'item_id' => $itemId],
                    ['quantidade' => 0.0000]
                );
                $estoqueOrigem->decrement('quantidade', $qtd);

                // 📦 2. Se for modo DIRETO, já incrementa no destino
                if ($modo === 'DIRETO') {
                    $estoqueDestino = EstoqueDeposito::firstOrCreate(
                        ['empresa_id' => $empresaId, 'deposito_id' => $destinoId, 'item_id' => $itemId],
                        ['quantidade' => 0.0000]
                    );
                    $estoqueDestino->increment('quantidade', $qtd);
                }
            }

            return $transferencia->load(['depositoOrigem', 'depositoDestino', 'itens.item']);
        });
    }

    /**
     * Recebimento e conclusão de carga que estava EM_TRANSITO
     */
    public function concluirTransferencia(int $transferenciaId, int $empresaId, int $recebedorId): Transferencia
    {
        return DB::transaction(function () use ($transferenciaId, $empresaId, $recebedorId) {
            $transferencia = Transferencia::with('itens')
                ->where('id', $transferenciaId)
                ->where('empresa_id', $empresaId)
                ->firstOrFail();

            if ($transferencia->status !== 'EM_TRANSITO') {
                throw new \Exception('Esta transferência não está em trânsito.');
            }

            // Incrementa no destino
            foreach ($transferencia->itens as $itemTrf) {
                $estoqueDestino = EstoqueDeposito::firstOrCreate(
                    ['empresa_id' => $empresaId, 'deposito_id' => $transferencia->deposito_destino_id, 'item_id' => $itemTrf->item_id],
                    ['quantidade' => 0.0000]
                );
                $estoqueDestino->increment('quantidade', $itemTrf->quantidade);
            }

            $transferencia->update([
                'status' => 'CONCLUIDA',
                'recebedor_id' => $recebedorId,
                'data_recebimento' => now(),
            ]);

            return $transferencia->load(['depositoOrigem', 'depositoDestino', 'itens.item']);
        });
    }
}