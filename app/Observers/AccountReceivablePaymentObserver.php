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
        // El status se actualiza automáticamente en el método recordPayment del modelo
    }

    /**
     * Handle the AccountReceivablePayment "updated" event.
     */
    public function updated(AccountReceivablePayment $accountReceivablePayment): void
    {
        // Actualizar el status de la cuenta por cobrar cuando se modifica un pago
        $accountReceivablePayment->accountReceivable->updateStatusAfterPayment();
    }

    /**
     * Handle the AccountReceivablePayment "deleted" event.
     */
    public function deleted(AccountReceivablePayment $accountReceivablePayment): void
    {
        // Actualizar el status de la cuenta por cobrar cuando se elimina un pago
        $accountReceivablePayment->accountReceivable->updateStatusAfterPayment();
    }

    /**
     * Handle the AccountReceivablePayment "restored" event.
     */
    public function restored(AccountReceivablePayment $accountReceivablePayment): void
    {
        // Actualizar el status de la cuenta por cobrar cuando se restaura un pago
        $accountReceivablePayment->accountReceivable->updateStatusAfterPayment();
    }

    /**
     * Handle the AccountReceivablePayment "force deleted" event.
     */
    public function forceDeleted(AccountReceivablePayment $accountReceivablePayment): void
    {
        // Actualizar el status de la cuenta por cobrar cuando se elimina permanentemente un pago
        $accountReceivablePayment->accountReceivable->updateStatusAfterPayment();
    }
}
