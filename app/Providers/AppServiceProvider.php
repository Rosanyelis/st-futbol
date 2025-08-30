<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\AccountReceivablePayment;
use App\Models\AccountPayablePayment;
use App\Observers\AccountReceivablePaymentObserver;
use App\Observers\AccountPayablePaymentObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Registrar observers para actualizar automáticamente el status de las cuentas
        AccountReceivablePayment::observe(AccountReceivablePaymentObserver::class);
        AccountPayablePayment::observe(AccountPayablePaymentObserver::class);
    }
}
