<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Modules\Fiscal\Contracts\FiscalDriverInterface;
use App\Modules\Fiscal\Drivers\MockFiscalDriver;
use App\Observers\AuditObserver;
use App\Modules\Produtos\Models\Item;
use App\Modules\OrdensServico\Models\OrdemServico;
use App\Modules\Vendas\Models\Venda;
use App\Modules\Financeiro\Models\LancamentoFinanceiro;
use App\Modules\Compras\Models\Compra;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Associa a Interface à implementação concreta
        $this->app->bind(FiscalDriverInterface::class, MockFiscalDriver::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // 🛡️ Conecta a Auditoria Automática aos Models do Core
        Item::observe(AuditObserver::class);
        OrdemServico::observe(AuditObserver::class);
        Venda::observe(AuditObserver::class);
        LancamentoFinanceiro::observe(AuditObserver::class);
        Compra::observe(AuditObserver::class);
    }
}