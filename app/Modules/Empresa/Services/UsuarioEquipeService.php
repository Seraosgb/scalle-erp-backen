<?php

namespace App\Modules\Empresa\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UsuarioEquipeService
{
    public function listarUsuarios(int $empresaId)
    {
        return User::where('empresa_id', $empresaId)
            ->select('id', 'empresa_id', 'name', 'email', 'role', 'ativo', 'created_at')
            ->orderBy('name')
            ->get();
    }

    public function criarUsuario(int $empresaId, array $data): User
    {
        return User::create([
            'empresa_id' => $empresaId,
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => strtoupper($data['role']),
            'ativo' => true,
        ]);
    }

    public function atualizarUsuario(int $id, int $empresaId, array $data): User
    {
        $user = User::where('id', $id)->where('empresa_id', $empresaId)->firstOrFail();

        $payload = [
            'name' => $data['name'] ?? $user->name,
            'role' => isset($data['role']) ? strtoupper($data['role']) : $user->role,
            'ativo' => $data['ativo'] ?? $user->ativo,
        ];

        if (!empty($data['password'])) {
            $payload['password'] = Hash::make($data['password']);
        }

        $user->update($payload);

        return $user;
    }

    public function inativarUsuario(int $id, int $empresaId, int $solicitanteId): bool
    {
        if ($id === $solicitanteId) {
            throw ValidationException::withMessages([
                'usuario' => ['Você não pode inativar seu próprio acesso.'],
            ]);
        }

        $user = User::where('id', $id)->where('empresa_id', $empresaId)->firstOrFail();
        return $user->update(['ativo' => false]);
    }
}