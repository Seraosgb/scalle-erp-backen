<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckTenantFeatureFlag
{
    public function handle(Request $request, Closure $next, string $moduloRequerido): Response
    {
        $user = $request->user();

        if (!$user || !$user->empresa_id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Usuário não vinculado a uma empresa ativa.'
            ], 403);
        }

        $empresa = $user->empresa()->with('plano')->first();

        if (!$empresa || !$empresa->plano) {
            return response()->json([
                'status' => 'error',
                'message' => 'Empresa sem plano ativo configurado.'
            ], 403);
        }

        // 1. Bloqueio por Inadimplência ou Cancelamento
        if (in_array($empresa->status_assinatura, ['INADIMPLENTE', 'CANCELADO'])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Acesso bloqueado temporariamente por pendência financeira. Regularize sua assinatura.'
            ], 403);
        }

        // 2. Verificação de Módulo Habilitado no Plano
        $modulos = $empresa->plano->modulos_habilitados ?? [];
        if (!in_array($moduloRequerido, $modulos)) {
            return response()->json([
                'status' => 'error',
                'message' => "O módulo [{$moduloRequerido}] não está disponível no plano {$empresa->plano->nome}. Faça upgrade para liberar o acesso."
            ], 403);
        }

        return $next($request);
    }
}