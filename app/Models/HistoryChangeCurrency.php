<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HistoryChangeCurrency extends Model
{
    protected $table = 'history_change_currencies';

    protected $fillable = [
            'bussines_id',
            'event_id',
            'currency_id',
            'method_payment_id',
            'date',
            'amount', // monto a cambiar en la moneda original
            'method_payment_receptor_id',
            'currency_receptor_id',
            'exchange_rate', // tasa de cambio aplicada
            'amount_converted', // monto recibido en la moneda de destino
            'description' // descripción opcional del cambio
    ];

    public function bussines()
    {
        return $this->belongsTo(Bussines::class, 'bussines_id', 'id');
    }

    public function event()
    {
        return $this->belongsTo(Event::class, 'event_id', 'id');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_id', 'id');
    }

    public function methodPayment()
    {
        return $this->belongsTo(MethodPayment::class, 'method_payment_id', 'id');
    }

    public function currencyReceptor()
    {
        return $this->belongsTo(Currency::class, 'currency_receptor_id', 'id');
    }

    public function methodPaymentReceptor()
    {
        return $this->belongsTo(MethodPayment::class, 'method_payment_receptor_id', 'id');
    }
}
