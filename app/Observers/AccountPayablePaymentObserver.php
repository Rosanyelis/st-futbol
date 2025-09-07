<?php

namespace App\Observers;

use App\Models\AccountPayablePayment;

class AccountPayablePaymentObserver
{
    /**
     * Handle the AccountPayablePayment "created" event.
     */
    public function created(AccountPayablePayment $accountPayablePayment): void
    {
        // Actualizar el status de la cuenta por pagar cuando se crea un pago
        $accountPayablePayment->accountPayable->withoutEvents(function() use ($accountPayablePayment) {
            $accountPayablePayment->accountPayable->updateStatusAfterPayment();
        });
    }

    /**
     * Handle the AccountPayablePayment "updated" event.
     */
    public function updated(AccountPayablePayment $accountPayablePayment): void
    {
        // Actualizar el status de la cuenta por pagar cuando se modifica un pago
        $accountPayablePayment->accountPayable->withoutEvents(function() use ($accountPayablePayment) {
            $accountPayablePayment->accountPayable->updateStatusAfterPayment();
        });
    }

    /**
     * Handle the AccountPayablePayment "deleted" event.
     */
    public function deleted(AccountPayablePayment $accountPayablePayment): void
    {
        // Actualizar el status de la cuenta por pagar cuando se elimina un pago
        $accountPayablePayment->accountPayable->withoutEvents(function() use ($accountPayablePayment) {
            $accountPayablePayment->accountPayable->updateStatusAfterPayment();
        });
    }

    /**
     * Handle the AccountPayablePayment "restored" event.
     */
    public function restored(AccountPayablePayment $accountPayablePayment): void
    {
        // Actualizar el status de la cuenta por pagar cuando se restaura un pago
        $accountPayablePayment->accountPayable->withoutEvents(function() use ($accountPayablePayment) {
            $accountPayablePayment->accountPayable->updateStatusAfterPayment();
        });
    }

    /**
     * Handle the AccountPayablePayment "force deleted" event.
     */
    public function forceDeleted(AccountPayablePayment $accountPayablePayment): void
    {
        // Actualizar el status de la cuenta por pagar cuando se elimina permanentemente un pago
        $accountPayablePayment->accountPayable->withoutEvents(function() use ($accountPayablePayment) {
            $accountPayablePayment->accountPayable->updateStatusAfterPayment();
        });
    }
}
