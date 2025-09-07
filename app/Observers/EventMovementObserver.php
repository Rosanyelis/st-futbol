<?php

namespace App\Observers;

use App\Models\EventMovement;
use App\Models\AccountReceivablePayment;
use App\Models\AccountPayablePayment;

class EventMovementObserver
{
    /**
     * Handle the EventMovement "created" event.
     */
    public function created(EventMovement $eventMovement): void
    {
        // Si es un ingreso relacionado con una cuenta por cobrar, crear el pago automáticamente
        if ($eventMovement->type === 'Ingreso' && $eventMovement->account_receivable_id) {
            $this->createAccountReceivablePayment($eventMovement);
        }
        
        // Si es un egreso relacionado con una cuenta por pagar, crear el pago automáticamente
        if ($eventMovement->type === 'Egreso' && $eventMovement->account_payable_id) {
            $this->createAccountPayablePayment($eventMovement);
        }
    }

    /**
     * Handle the EventMovement "updated" event.
     */
    public function updated(EventMovement $eventMovement): void
    {
        // Si se actualiza un movimiento que tiene un pago asociado, actualizar el pago
        if ($eventMovement->account_receivable_payment_id) {
            $this->updateAccountReceivablePayment($eventMovement);
        }
        
        if ($eventMovement->account_payable_payment_id) {
            $this->updateAccountPayablePayment($eventMovement);
        }
    }

    /**
     * Handle the EventMovement "deleted" event.
     */
    public function deleted(EventMovement $eventMovement): void
    {
        // Si se elimina un movimiento que tiene un pago asociado, eliminar el pago
        if ($eventMovement->account_receivable_payment_id) {
            $payment = AccountReceivablePayment::find($eventMovement->account_receivable_payment_id);
            if ($payment) {
                $payment->delete();
            }
        }
        
        if ($eventMovement->account_payable_payment_id) {
            $payment = AccountPayablePayment::find($eventMovement->account_payable_payment_id);
            if ($payment) {
                $payment->delete();
            }
        }
    }

    /**
     * Crear un pago de cuenta por cobrar basado en el movimiento del evento
     */
    private function createAccountReceivablePayment(EventMovement $eventMovement): void
    {
        try {
            $payment = AccountReceivablePayment::create([
                'account_receivable_id' => $eventMovement->account_receivable_id,
                'date' => $eventMovement->date,
                'amount' => $eventMovement->amount,
                'description' => $eventMovement->description ?? "Pago registrado desde movimiento de evento #{$eventMovement->id}",
            ]);

            // Actualizar el movimiento con el ID del pago creado
            // Usar withoutEvents para evitar bucles infinitos
            $eventMovement->withoutEvents(function() use ($eventMovement, $payment) {
                $eventMovement->update(['account_receivable_payment_id' => $payment->id]);
            });
            
            // El estado de la cuenta por cobrar se actualiza automáticamente a través del observer de AccountReceivablePayment
            
        } catch (\Exception $e) {
            \Log::error('Error al crear pago de cuenta por cobrar desde EventMovement: ' . $e->getMessage());
        }
    }

    /**
     * Crear un pago de cuenta por pagar basado en el movimiento del evento
     */
    private function createAccountPayablePayment(EventMovement $eventMovement): void
    {
        try {
            $payment = AccountPayablePayment::create([
                'account_payable_id' => $eventMovement->account_payable_id,
                'date' => $eventMovement->date,
                'amount' => $eventMovement->amount,
                'description' => $eventMovement->description ?? "Pago registrado desde movimiento de evento #{$eventMovement->id}",
            ]);

            // Actualizar el movimiento con el ID del pago creado
            // Usar withoutEvents para evitar bucles infinitos
            $eventMovement->withoutEvents(function() use ($eventMovement, $payment) {
                $eventMovement->update(['account_payable_payment_id' => $payment->id]);
            });
            
            // El estado de la cuenta por pagar se actualiza automáticamente a través del observer de AccountPayablePayment
            
        } catch (\Exception $e) {
            \Log::error('Error al crear pago de cuenta por pagar desde EventMovement: ' . $e->getMessage());
        }
    }

    /**
     * Actualizar un pago de cuenta por cobrar cuando se modifica el movimiento
     */
    private function updateAccountReceivablePayment(EventMovement $eventMovement): void
    {
        try {
            $payment = AccountReceivablePayment::find($eventMovement->account_receivable_payment_id);
            if ($payment) {
                $payment->update([
                    'date' => $eventMovement->date,
                    'amount' => $eventMovement->amount,
                    'description' => $eventMovement->description ?? $payment->description,
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Error al actualizar pago de cuenta por cobrar desde EventMovement: ' . $e->getMessage());
        }
    }

    /**
     * Actualizar un pago de cuenta por pagar cuando se modifica el movimiento
     */
    private function updateAccountPayablePayment(EventMovement $eventMovement): void
    {
        try {
            $payment = AccountPayablePayment::find($eventMovement->account_payable_payment_id);
            if ($payment) {
                $payment->update([
                    'date' => $eventMovement->date,
                    'amount' => $eventMovement->amount,
                    'description' => $eventMovement->description ?? $payment->description,
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Error al actualizar pago de cuenta por pagar desde EventMovement: ' . $e->getMessage());
        }
    }
}
