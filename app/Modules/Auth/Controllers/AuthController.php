<?php

namespace App\Modules\Auth\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::with('empresa')->where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['As credenciais fornecidas estão incorretas.'],
            ]);
        }

        if (! $user->ativo || ! $user->empresa->ativo) {
            return response()->json([
                'status' => 'error',
                'message' => 'Conta ou empresa inativa. Entre em contato com o suporte.'
            ], 403);
        }

        // Revoga tokens antigos e gera um novo
        $user->tokens()->delete();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'data' => [
                'token' => $token,
                'token_type' => 'Bearer',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'empresa' => [
                        'id' => $user->empresa->id,
                        'nome_fantasia' => $user->empresa->nome_fantasia ?? $user->empresa->razao_social,
                    ]
                ]
            ]
        ]);
    }
}