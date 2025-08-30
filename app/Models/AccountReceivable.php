<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AccountReceivable extends Model
{
    use HasFactory;
    protected $fillable = [
        'club_id',
        'event_id',
        'currency_id',
        'supplier_id',
        'date',
        'has_accommodation',
        'players_quantity',
        'player_price',
        'total_players',
        'teachers_quantity',
        'teacher_price',
        'total_teachers',
        'companions_quantity',
        'companion_price',
        'total_companions',
        'drivers_quantity',
        'driver_price',
        'total_drivers',
        'liberated_quantity',
        'total_people',
        'total_amount',
        'description',
        'status',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function club()
    {
        return $this->belongsTo(Club::class, 'club_id', 'id');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function payments()
    {
        return $this->hasMany(AccountReceivablePayment::class);
    }

    /**
     * Registrar un pago para esta cuenta por cobrar
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
     * Actualizar el status de la cuenta por cobrar después de un pago
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
        return $this->total_amount - $this->getPaidAmount();
    }

    /**
     * Obtener el porcentaje de pago
     */
    public function getPaymentPercentage()
    {
        if ($this->total_amount == 0) return 0;
        return round(($this->getPaidAmount() / $this->total_amount) * 100, 2);
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
