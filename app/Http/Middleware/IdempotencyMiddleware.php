<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class IdempotencyMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $idempotencyKey = $request->header('Idempotency-Key');

        if (! $idempotencyKey) {
            return $next($request);
        }

        $empresaId = $request->user()?->empresa_id ?? 'global';
        $cacheKey = "idempotency:{$empresaId}:{$idempotencyKey}";

        // Se a chave já existe no cache, devolve a resposta anterior imediatamente
        if (Cache::has($cacheKey)) {
            $cached = Cache::get($cacheKey);
            return response()->json($cached['body'], $cached['status'], [
                'X-Idempotent-Replayed' => 'true'
            ]);
        }

        $response = $next($request);

        // Armazena no cache apenas requisições bem-sucedidas (200-299) por 120 segundos
        if ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300) {
            $content = json_decode($response->getContent(), true) ?? $response->getContent();
            Cache::put($cacheKey, [
                'status' => $response->getStatusCode(),
                'body' => $content
            ], 120);
        }

        return $response;
    }
}