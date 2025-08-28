<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class AccountPayable extends Model
{
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
        return $this->payments()->create([
            'date' => $date,
            'amount' => $amount,
            'description' => $description,
        ]);
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
            return $payment->delete();
        }
        return false;
    }
}
