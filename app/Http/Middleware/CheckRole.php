<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user || ! in_array($user->role, $roles)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Acesso negado. Seu perfil de usuário (' . ($user->role ?? 'NENHUM') . ') não tem permissão para esta ação.'
            ], 403);
        }

        return $next($request);
    }
}