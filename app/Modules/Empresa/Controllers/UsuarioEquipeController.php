<?php

namespace App\Modules\Empresa\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Empresa\Services\UsuarioEquipeService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class UsuarioEquipeController extends Controller
{
    public function __construct(private UsuarioEquipeService $service) {}

    public function index(Request $request): JsonResponse
    {
        $usuarios = $this->service->listarUsuarios($request->user()->empresa_id);
        return response()->json(['status' => 'success', 'data' => $usuarios]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'role' => 'required|in:ADMIN,FINANCEIRO,TECNICO,ATENDENTE',
        ]);

        $usuario = $this->service->criarUsuario($request->user()->empresa_id, $validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Colaborador cadastrado com sucesso!',
            'data' => $usuario
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'password' => 'nullable|string|min:6',
            'role' => 'nullable|in:ADMIN,FINANCEIRO,TECNICO,ATENDENTE',
            'ativo' => 'nullable|boolean',
        ]);

        $usuario = $this->service->atualizarUsuario($id, $request->user()->empresa_id, $validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Dados do colaborador atualizados com sucesso!',
            'data' => $usuario
        ]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $this->service->inativarUsuario($id, $request->user()->empresa_id, $request->user()->id);

        return response()->json([
            'status' => 'success',
            'message' => 'Colaborador inativado com sucesso!'
        ]);
    }
}