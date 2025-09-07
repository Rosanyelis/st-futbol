<?php

namespace App\Observers;

use App\Models\AccountReceivablePayment;

class AccountReceivablePaymentObserver
{
    /**
     * Handle the AccountReceivablePayment "created" event.
     */
    public function created(AccountReceivablePayment $accountReceivablePayment): void
    {
        // Actualizar el estado de la cuenta por cobrar cuando se crea un pago
        // Usar withoutEvents para evitar bucles infinitos
        $accountReceivablePayment->accountReceivable->withoutEvents(function() use ($accountReceivablePayment) {
            $accountReceivablePayment->accountReceivable->updateStatusAfterPayment();
        });
    }

    /**
     * Handle the AccountReceivablePayment "updated" event.
     */
    public function updated(AccountReceivablePayment $accountReceivablePayment): void
    {
        // Actualizar el estado de la cuenta por cobrar cuando se modifica un pago
        // Usar withoutEvents para evitar bucles infinitos
        $accountReceivablePayment->accountReceivable->withoutEvents(function() use ($accountReceivablePayment) {
            $accountReceivablePayment->accountReceivable->updateStatusAfterPayment();
        });
    }

    /**
     * Handle the AccountReceivablePayment "deleted" event.
     */
    public function deleted(AccountReceivablePayment $accountReceivablePayment): void
    {
        // Actualizar el estado de la cuenta por cobrar cuando se elimina un pago
        // Usar withoutEvents para evitar bucles infinitos
        $accountReceivablePayment->accountReceivable->withoutEvents(function() use ($accountReceivablePayment) {
            $accountReceivablePayment->accountReceivable->updateStatusAfterPayment();
        });
    }

    /**
     * Handle the AccountReceivablePayment "restored" event.
     */
    public function restored(AccountReceivablePayment $accountReceivablePayment): void
    {
        // Actualizar el estado de la cuenta por cobrar cuando se restaura un pago
        // Usar withoutEvents para evitar bucles infinitos
        $accountReceivablePayment->accountReceivable->withoutEvents(function() use ($accountReceivablePayment) {
            $accountReceivablePayment->accountReceivable->updateStatusAfterPayment();
        });
    }

    /**
     * Handle the AccountReceivablePayment "force deleted" event.
     */
    public function forceDeleted(AccountReceivablePayment $accountReceivablePayment): void
    {
        // Actualizar el estado de la cuenta por cobrar cuando se elimina permanentemente un pago
        // Usar withoutEvents para evitar bucles infinitos
        $accountReceivablePayment->accountReceivable->withoutEvents(function() use ($accountReceivablePayment) {
            $accountReceivablePayment->accountReceivable->updateStatusAfterPayment();
        });
    }
}
