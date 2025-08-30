<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\AccountReceivable;
use App\Models\AccountPayable;
use App\Models\AccountReceivablePayment;
use App\Models\AccountPayablePayment;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AccountStatusUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_account_receivable_status_updates_when_payment_is_completed()
    {
        // Crear una cuenta por cobrar simple
        $accountReceivable = AccountReceivable::create([
            'club_id' => 1,
            'event_id' => 1,
            'currency_id' => 1,
            'total_amount' => 1000,
            'status' => 'Pendiente'
        ]);

        // Verificar que inicialmente está pendiente
        $this->assertEquals('Pendiente', $accountReceivable->status);

        // Registrar un pago parcial
        $accountReceivable->recordPayment(500, now());
        $accountReceivable->refresh();

        // Verificar que cambió a "En Proceso"
        $this->assertEquals('En Proceso', $accountReceivable->status);

        // Registrar el pago restante
        $accountReceivable->recordPayment(500, now());
        $accountReceivable->refresh();

        // Verificar que cambió a "Completado"
        $this->assertEquals('Completado', $accountReceivable->status);
    }

    public function test_account_payable_status_updates_when_payment_is_completed()
    {
        // Crear una cuenta por pagar simple
        $accountPayable = AccountPayable::create([
            'supplier_id' => 1,
            'event_id' => 1,
            'currency_id' => 1,
            'amount' => 1000,
            'status' => 'Pendiente'
        ]);

        // Verificar que inicialmente está pendiente
        $this->assertEquals('Pendiente', $accountPayable->status);

        // Registrar un pago parcial
        $accountPayable->recordPayment(500, now());
        $accountPayable->refresh();

        // Verificar que cambió a "En Proceso"
        $this->assertEquals('En Proceso', $accountPayable->status);

        // Registrar el pago restante
        $accountPayable->recordPayment(500, now());
        $accountPayable->refresh();

        // Verificar que cambió a "Completado"
        $this->assertEquals('Completado', $accountPayable->status);
    }
}
