<?php

namespace App\Traits;

use App\Scopes\TenantScope;
use Illuminate\Support\Facades\Auth;

trait BelongsToTenant
{
    protected static function bootBelongsToTenant(): void
    {
        static::addGlobalScope(new TenantScope());

        static::creating(function ($model) {
            if (Auth::check() && Auth::user()->empresa_id && empty($model->empresa_id)) {
                $model->empresa_id = Auth::user()->empresa_id;
            }
        });
    }
}