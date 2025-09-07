<?php

namespace App\Observers;

use App\Models\AccountPayable;

class AccountPayableObserver
{
    /**
     * Handle the AccountPayable "created" event.
     */
    public function created(AccountPayable $accountPayable): void
    {
        // No es necesario hacer nada al crear una cuenta por pagar
    }

    /**
     * Handle the AccountPayable "updated" event.
     */
    public function updated(AccountPayable $accountPayable): void
    {
        // Si cambió el monto total, actualizar el status basado en los pagos existentes
        if ($accountPayable->isDirty('amount') && !$accountPayable->isDirty('status')) {
            $accountPayable->withoutEvents(function() use ($accountPayable) {
                $accountPayable->updateStatusAfterPayment();
            });
        }
    }

    /**
     * Handle the AccountPayable "deleted" event.
     */
    public function deleted(AccountPayable $accountPayable): void
    {
        // No es necesario hacer nada al eliminar una cuenta por pagar
    }

    /**
     * Handle the AccountPayable "restored" event.
     */
    public function restored(AccountPayable $accountPayable): void
    {
        // Al restaurar una cuenta por pagar, actualizar el status basado en los pagos existentes
        $accountPayable->withoutEvents(function() use ($accountPayable) {
            $accountPayable->updateStatusAfterPayment();
        });
    }

    /**
     * Handle the AccountPayable "force deleted" event.
     */
    public function forceDeleted(AccountPayable $accountPayable): void
    {
        // No es necesario hacer nada al eliminar permanentemente una cuenta por pagar
    }
}
