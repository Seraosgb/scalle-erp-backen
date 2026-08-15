<?php

namespace App\Observers;

use App\Models\AuditoriaLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditObserver
{
    public function created(Model $model): void
    {
        $this->registrarLog($model, 'CRIACAO', null, $model->getAttributes());
    }

    public function updated(Model $model): void
    {
        $dadosAntigos = array_intersect_key($model->getOriginal(), $model->getDirty());
        $dadosNovos = $model->getDirty();

        $this->registrarLog($model, 'ATUALIZACAO', $dadosAntigos, $dadosNovos);
    }

    public function deleted(Model $model): void
    {
        $this->registrarLog($model, 'EXCLUSAO', $model->getOriginal(), null);
    }

    private function registrarLog(Model $model, string $acao, ?array $dadosAntigos, ?array $dadosNovos): void
    {
        $empresaId = $model->empresa_id ?? Auth::user()?->empresa_id;

        if (! $empresaId) {
            return;
        }

        AuditoriaLog::create([
            'empresa_id' => $empresaId,
            'user_id' => Auth::id(),
            'acao' => $acao,
            'tabela' => $model->getTable(),
            'registro_id' => $model->getKey(),
            'dados_antigos' => $dadosAntigos,
            'dados_novos' => $dadosNovos,
            'ip_address' => Request::ip(),
        ]);
    }
}