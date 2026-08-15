<?php

namespace App\Services;

use App\Models\Empresa;
use App\Models\StorageArquivo;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Exception;

class StorageQuotaService
{
    public function validarEArmazenar(int $empresaId, int $userId, UploadedFile $file, string $modulo, string $caminhoDestino): StorageArquivo
    {
        return DB::transaction(function () use ($empresaId, $userId, $file, $modulo, $caminhoDestino) {
            $empresa = Empresa::with('plano')->where('id', $empresaId)->lockForUpdate()->firstOrFail();

            $limiteBytes = (int) ($empresa->plano->limite_storage_bytes ?? 3221225472);
            $usoAtualBytes = (int) ($empresa->storage_utilizado_bytes ?? 0);
            $tamanhoArquivoBytes = $file->getSize();

            // Validação estrita de cota
            if (($usoAtualBytes + $tamanhoArquivoBytes) > $limiteBytes) {
                $limiteGB = round($limiteBytes / (1024 * 1024 * 1024), 2);
                throw new Exception("Cota de armazenamento excedida! Seu plano permite até {$limiteGB} GB. Faça upgrade para continuar enviando arquivos.");
            }

            // Armazena no disco local/nuvem
            $caminhoSalvo = $file->store($caminhoDestino, 'public');

            // Incrementa consumo na empresa
            $empresa->increment('storage_utilizado_bytes', $tamanhoArquivoBytes);

            // Grava registro de auditoria do arquivo
            return StorageArquivo::create([
                'empresa_id' => $empresaId,
                'user_id' => $userId,
                'modulo' => $modulo,
                'nome_original' => $file->getClientOriginalName(),
                'caminho_storage' => $caminhoSalvo,
                'tamanho_bytes' => $tamanhoArquivoBytes,
                'mime_type' => $file->getClientMimeType(),
            ]);
        });
    }
}