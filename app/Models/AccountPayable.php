<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class AccountPayable extends Model
{
    use HasFactory;
    protected $fillable = [
        'supplier_id',
        'event_id',
        'currency_id',
        'date',
        'amount',
        'description',
        'status',
    ];

    protected $casts = [
        'date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function payments()
    {
        return $this->hasMany(AccountPayablePayment::class);
    }

    /**
     * Registrar un pago para esta cuenta por pagar
     */
    public function recordPayment($amount, $date, $reference = null, $description = null)
    {
        $payment = $this->payments()->create([
            'date' => $date,
            'amount' => $amount,
            'description' => $description,
        ]);
        
        // Actualizar el status después de registrar el pago
        $this->updateStatusAfterPayment();
        
        return $payment;
    }

    /**
     * Actualizar el status de la cuenta por pagar después de un pago
     */
    public function updateStatusAfterPayment()
    {
        $pendingAmount = $this->getPendingAmount();
        
        if ($pendingAmount <= 0) {
            // Si no hay monto pendiente, marcar como completado
            $this->update(['status' => 'Completado']);
        } elseif ($this->getPaymentPercentage() > 0) {
            // Si hay pagos parciales, marcar como en proceso
            $this->update(['status' => 'En Proceso']);
        } else {
            // Si no hay pagos, marcar como pendiente
            $this->update(['status' => 'Pendiente']);
        }
    }

    /**
     * Obtener el monto total pagado
     */
    public function getPaidAmount()
    {
        return $this->payments->sum('amount');
    }

    /**
     * Obtener el monto pendiente
     */
    public function getPendingAmount()
    {
        return $this->amount - $this->getPaidAmount();
    }

    /**
     * Obtener el porcentaje de pago
     */
    public function getPaymentPercentage()
    {
        if ($this->amount <= 0) return 0;
        return round(($this->getPaidAmount() / $this->amount) * 100, 2);
    }

    /**
     * Actualizar un pago existente
     */
    public function updatePayment($paymentId, $amount, $date, $reference = null, $description = null)
    {
        $payment = $this->payments()->find($paymentId);
        if ($payment) {
            $payment->update([
                'date' => $date,
                'amount' => $amount,
            ]);
            
            // Actualizar el status después de modificar el pago
            $this->updateStatusAfterPayment();
            
            return $payment;
        }
        return null;
    }

    /**
     * Eliminar un pago específico
     */
    public function deletePayment($paymentId)
    {
        $payment = $this->payments()->find($paymentId);
        if ($payment) {
            $deleted = $payment->delete();
            
            // Actualizar el status después de eliminar el pago
            if ($deleted) {
                $this->updateStatusAfterPayment();
            }
            
            return $deleted;
        }
        return false;
    }
}
