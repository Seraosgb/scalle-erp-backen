<?php

namespace App\Modules\Financeiro\Services;

use App\Modules\Financeiro\Models\CategoriaFinanceira;

class CategoriaFinanceiraService
{
    public function listar(int $empresaId, ?string $tipo = null)
    {
        $query = CategoriaFinanceira::where('empresa_id', $empresaId)->where('ativo', true);

        if ($tipo) {
            $query->where('tipo', strtoupper($tipo));
        }

        return $query->orderBy('nome')->get();
    }

    public function criar(int $empresaId, array $data): CategoriaFinanceira
    {
        return CategoriaFinanceira::create([
            'empresa_id' => $empresaId,
            'nome' => $data['nome'],
            'tipo' => strtoupper($data['tipo']),
            'ativo' => true,
        ]);
    }

    public function inativar(int $id, int $empresaId): bool
    {
        $cat = CategoriaFinanceira::where('id', $id)->where('empresa_id', $empresaId)->firstOrFail();
        return $cat->update(['ativo' => false]);
    }
}