<?php

namespace App\Modules\Produtos\Services;

use App\Modules\Produtos\Models\Categoria;

class CategoriaService
{
    public function listar(int $empresaId)
    {
        return Categoria::where('empresa_id', $empresaId)
            ->where('ativo', true)
            ->orderBy('nome')
            ->get();
    }

    public function criar(int $empresaId, array $data): Categoria
    {
        return Categoria::create([
            'empresa_id' => $empresaId,
            'nome' => $data['nome'],
            'ativo' => true,
        ]);
    }

    public function inativar(int $id, int $empresaId): bool
    {
        $categoria = Categoria::where('id', $id)->where('empresa_id', $empresaId)->firstOrFail();
        return $categoria->update(['ativo' => false]);
    }
}