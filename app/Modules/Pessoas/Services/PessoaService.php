<?php

namespace App\Modules\Pessoas\Services;

use App\Modules\Pessoas\Models\Pessoa;
use App\Modules\Pessoas\DTOs\PessoaDTO;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PessoaService
{
    public function criarPessoa(PessoaDTO $dto): Pessoa
    {
        return Pessoa::create($dto->toArray());
    }

    public function listarPessoas(int $empresaId, array $filtros = []): LengthAwarePaginator
    {
        $query = Pessoa::where('empresa_id', $empresaId);

        if (!empty($filtros['busca'])) {
            $busca = $filtros['busca'];
            $query->where(function ($q) use ($busca) {
                $q->where('nome_razao', 'like', "%{$busca}%")
                  ->orWhere('nome_fantasia', 'like', "%{$busca}%")
                  ->orWhere('cpf_cnpj', 'like', "%{$busca}%");
            });
        }

        if (isset($filtros['tipo'])) {
            if ($filtros['tipo'] === 'cliente') $query->where('is_cliente', true);
            if ($filtros['tipo'] === 'fornecedor') $query->where('is_fornecedor', true);
        }

        return $query->where('ativo', true)->orderBy('nome_razao')->paginate(15);
    }

    public function buscarPorId(int $id, int $empresaId): ?Pessoa
    {
        return Pessoa::where('id', $id)
            ->where('empresa_id', $empresaId)
            ->first();
    }

    public function atualizarPessoa(Pessoa $pessoa, PessoaDTO $dto): Pessoa
    {
        $pessoa->update($dto->toArray());
        return $pessoa;
    }

    public function inativarPessoa(Pessoa $pessoa): bool
    {
        return $pessoa->update(['ativo' => false]);
    }
}