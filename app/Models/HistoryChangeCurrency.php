<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class HistoryChangeCurrency extends Model
{
    protected $fillable = [
        'currency_id',
        'method_payment_id',
        'date',
        'amount',
        'method_payment_receptor_id',
        'currency_receptor_id',
        'exchange_rate',
        'type_operation',
        'amount_converted',
        'description',
    ];

    protected $casts = [
        'date' => 'date',
        'amount' => 'decimal:2',
        'exchange_rate' => 'decimal:2',
        'amount_converted' => 'decimal:2',
    ];

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function methodPayment()
    {
        return $this->belongsTo(MethodPayment::class);
    }

    public function currencyReceptor()
    {
        return $this->belongsTo(Currency::class, 'currency_receptor_id');
    }

    public function methodPaymentReceptor()
    {
        return $this->belongsTo(MethodPayment::class, 'method_payment_receptor_id');
    }

    /**
     * Calcular el monto convertido basado en la tasa de cambio
     */
    public function calculateConvertedAmount()
    {
        if ($this->exchange_rate && $this->amount) {
            return $this->amount * $this->exchange_rate;
        }
        return 0;
    }

    /**
     * Obtener el nombre formateado de la moneda origen
     */
    public function getOriginCurrencyName()
    {
        return $this->currency ? $this->currency->name : 'N/A';
    }

    /**
     * Obtener el nombre formateado de la moneda destino
     */
    public function getDestinationCurrencyName()
    {
        return $this->currencyReceptor ? $this->currencyReceptor->name : 'N/A';
    }

    /**
     * Obtener el nombre del método de pago origen
     */
    public function getOriginMethodPaymentName()
    {
        return $this->methodPayment ? $this->methodPayment->account_holder. ' - '. $this->methodPayment->currency->name . ' - '. $this->methodPayment->entity->name : 'N/A';
    }

    /**
     * Obtener el nombre del método de pago destino
     */
    public function getDestinationMethodPaymentName()
    {
        return $this->methodPaymentReceptor ? $this->methodPaymentReceptor->account_holder . ' - '. $this->methodPaymentReceptor->currency->name . ' - '. $this->methodPaymentReceptor->entity->name : 'N/A';
    }
}
