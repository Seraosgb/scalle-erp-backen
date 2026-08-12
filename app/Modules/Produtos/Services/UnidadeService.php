<?php

namespace App\Modules\Produtos\Services;

use App\Modules\Produtos\Models\Unidade;

class UnidadeService
{
    public function listar(int $empresaId)
    {
        return Unidade::where('empresa_id', $empresaId)
            ->where('ativo', true)
            ->orderBy('sigla')
            ->get();
    }

    public function criar(int $empresaId, array $data): Unidade
    {
        return Unidade::create([
            'empresa_id' => $empresaId,
            'sigla' => strtoupper($data['sigla']),
            'nome' => $data['nome'],
            'ativo' => true,
        ]);
    }

    public function inativar(int $id, int $empresaId): bool
    {
        $unidade = Unidade::where('id', $id)->where('empresa_id', $empresaId)->firstOrFail();
        return $unidade->update(['ativo' => false]);
    }
}