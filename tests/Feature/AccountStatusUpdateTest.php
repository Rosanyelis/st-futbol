<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Club;
use App\Models\Event;
use App\Models\Currency;
use App\Models\AccountReceivable;
use App\Models\AccountReceivablePayment;
use App\Models\EventMovement;
use App\Models\MethodPayment;
use App\Models\CategoryIncome;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AccountStatusUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_account_receivable_status_updates_when_payments_match_total()
    {
        // Crear datos de prueba
        $club = Club::factory()->create();
        $event = Event::factory()->create();
        $currency = Currency::factory()->create();
        
        $accountReceivable = AccountReceivable::create([
            'club_id' => $club->id,
            'event_id' => $event->id,
            'currency_id' => $currency->id,
            'date' => now()->format('Y-m-d'),
            'total_amount' => 1000.00,
            'status' => 'Pendiente',
            'players_quantity' => 10,
            'player_price' => 100.00,
            'total_players' => 1000.00,
            'teachers_quantity' => 0,
            'teacher_price' => 0,
            'total_teachers' => 0,
            'companions_quantity' => 0,
            'companion_price' => 0,
            'total_companions' => 0,
            'drivers_quantity' => 0,
            'driver_price' => 0,
            'total_drivers' => 0,
            'liberated_quantity' => 0,
            'liberated_price' => 0,
            'total_liberated' => 0,
            'total_people' => 10,
            'has_accommodation' => false,
        ]);

        // Verificar estado inicial
        $this->assertEquals('Pendiente', $accountReceivable->fresh()->status);

        // Crear primer pago parcial
        AccountReceivablePayment::create([
            'account_receivable_id' => $accountReceivable->id,
            'date' => now(),
            'amount' => 500.00,
            'description' => 'Pago parcial'
        ]);

        // Verificar que el estado sigue siendo "Pendiente" porque no se ha completado el total
        $this->assertEquals('Pendiente', $accountReceivable->fresh()->status);

        // Crear segundo pago que complete el total
        AccountReceivablePayment::create([
            'account_receivable_id' => $accountReceivable->id,
            'date' => now(),
            'amount' => 500.00,
            'description' => 'Pago final'
        ]);

        // Verificar que el estado cambió a "Completado" porque los pagos igualan el total
        $this->assertEquals('Completado', $accountReceivable->fresh()->status);
    }

    public function test_account_receivable_status_updates_when_total_amount_changes()
    {
        // Crear datos de prueba
        $club = Club::factory()->create();
        $event = Event::factory()->create();
        $currency = Currency::factory()->create();
        
        $accountReceivable = AccountReceivable::create([
            'club_id' => $club->id,
            'event_id' => $event->id,
            'currency_id' => $currency->id,
            'date' => now()->format('Y-m-d'),
            'total_amount' => 1000.00,
            'status' => 'Pendiente',
            'players_quantity' => 10,
            'player_price' => 100.00,
            'total_players' => 1000.00,
            'teachers_quantity' => 0,
            'teacher_price' => 0,
            'total_teachers' => 0,
            'companions_quantity' => 0,
            'companion_price' => 0,
            'total_companions' => 0,
            'drivers_quantity' => 0,
            'driver_price' => 0,
            'total_drivers' => 0,
            'liberated_quantity' => 0,
            'liberated_price' => 0,
            'total_liberated' => 0,
            'total_people' => 10,
            'has_accommodation' => false,
        ]);

        // Crear pagos que sumen 500
        AccountReceivablePayment::create([
            'account_receivable_id' => $accountReceivable->id,
            'date' => now(),
            'amount' => 500.00,
            'description' => 'Pago parcial'
        ]);

        // Verificar que el estado sigue siendo "Pendiente" porque los pagos no coinciden con el total
        $this->assertEquals('Pendiente', $accountReceivable->fresh()->status);

        // Actualizar el total_amount para que coincida con los pagos
        $accountReceivable->update(['total_amount' => 500.00]);

        // Verificar que el estado cambió a "Completado" porque ahora los pagos igualan el total
        $this->assertEquals('Completado', $accountReceivable->fresh()->status);

        // Cambiar el total_amount a un valor mayor
        $accountReceivable->update(['total_amount' => 1000.00]);

        // Verificar que el estado volvió a "Pendiente" porque los pagos ya no igualan el total
        $this->assertEquals('Pendiente', $accountReceivable->fresh()->status);
    }

    public function test_account_receivable_status_updates_when_payment_is_deleted()
    {
        // Crear datos de prueba
        $club = Club::factory()->create();
        $event = Event::factory()->create();
        $currency = Currency::factory()->create();
        
        $accountReceivable = AccountReceivable::create([
            'club_id' => $club->id,
            'event_id' => $event->id,
            'currency_id' => $currency->id,
            'date' => now()->format('Y-m-d'),
            'total_amount' => 1000.00,
            'status' => 'Pendiente',
            'players_quantity' => 10,
            'player_price' => 100.00,
            'total_players' => 1000.00,
            'teachers_quantity' => 0,
            'teacher_price' => 0,
            'total_teachers' => 0,
            'companions_quantity' => 0,
            'companion_price' => 0,
            'total_companions' => 0,
            'drivers_quantity' => 0,
            'driver_price' => 0,
            'total_drivers' => 0,
            'liberated_quantity' => 0,
            'liberated_price' => 0,
            'total_liberated' => 0,
            'total_people' => 10,
            'has_accommodation' => false,
        ]);

        // Crear pagos que sumen exactamente el total
        $payment1 = AccountReceivablePayment::create([
            'account_receivable_id' => $accountReceivable->id,
            'date' => now(),
            'amount' => 500.00,
            'description' => 'Pago parcial 1'
        ]);

        $payment2 = AccountReceivablePayment::create([
            'account_receivable_id' => $accountReceivable->id,
            'date' => now(),
            'amount' => 500.00,
            'description' => 'Pago parcial 2'
        ]);

        // Verificar que el estado es "Completado" porque los pagos igualan el total
        $this->assertEquals('Completado', $accountReceivable->fresh()->status);

        // Eliminar un pago
        $payment1->delete();

        // Verificar que el estado cambió a "Pendiente" porque los pagos ya no igualan el total
        $this->assertEquals('Pendiente', $accountReceivable->fresh()->status);
    }
}