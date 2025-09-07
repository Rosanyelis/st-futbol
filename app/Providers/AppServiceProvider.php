<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\AccountReceivable;
use App\Models\AccountReceivablePayment;
use App\Models\AccountPayable;
use App\Models\AccountPayablePayment;
use App\Models\EventMovement;
use App\Observers\AccountReceivableObserver;
use App\Observers\AccountReceivablePaymentObserver;
use App\Observers\AccountPayableObserver;
use App\Observers\AccountPayablePaymentObserver;
use App\Observers\EventMovementObserver;

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
        AccountReceivable::observe(AccountReceivableObserver::class);
        AccountReceivablePayment::observe(AccountReceivablePaymentObserver::class);
        AccountPayable::observe(AccountPayableObserver::class);
        AccountPayablePayment::observe(AccountPayablePaymentObserver::class);
        EventMovement::observe(EventMovementObserver::class);
    }
}
