<?php

namespace App\Observers;

use App\Models\AccountReceivable;

class AccountReceivableObserver
{
    /**
     * Handle the AccountReceivable "updated" event.
     */
    public function updated(AccountReceivable $accountReceivable): void
    {
        // Solo actualizar el estado si cambió el campo total_amount
        // y evitar bucles infinitos verificando que no se esté actualizando el status
        if ($accountReceivable->isDirty('total_amount') && !$accountReceivable->isDirty('status')) {
            // Usar withoutEvents para evitar que se dispare el observer nuevamente
            $accountReceivable->withoutEvents(function() use ($accountReceivable) {
                $accountReceivable->updateStatusAfterPayment();
            });
        }
    }
}
