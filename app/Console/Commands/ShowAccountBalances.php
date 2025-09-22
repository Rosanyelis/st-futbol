<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\MethodPayment;
use App\Models\BussinesMovement;
use App\Models\EventMovement;

class ShowAccountBalances extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'accounts:show-balances {--currency= : Filter by currency ID} {--entity= : Filter by entity ID}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Muestra un resumen de los saldos actuales de todas las cuentas/métodos de pago';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('💰 Resumen de Saldos de Cuentas');
        $this->line('================================');

        $query = MethodPayment::with(['currency', 'entity']);

        // Aplicar filtros si se especifican
        if ($currencyId = $this->option('currency')) {
            $query->where('currency_id', $currencyId);
        }

        if ($entityId = $this->option('entity')) {
            $query->where('entity_id', $entityId);
        }

        $methodPayments = $query->get();

        if ($methodPayments->isEmpty()) {
            $this->warn('No se encontraron métodos de pago con los filtros especificados.');
            return;
        }

        $this->newLine();
        $this->info("📊 Total de cuentas: {$methodPayments->count()}");
        $this->newLine();

        // Crear tabla
        $headers = ['ID', 'Titular', 'Tipo', 'Entidad', 'Moneda', 'Saldo Actual', 'Saldo Inicial'];
        $rows = [];

        $totalBalance = 0;

        foreach ($methodPayments as $methodPayment) {
            $rows[] = [
                $methodPayment->id,
                $methodPayment->account_holder,
                $methodPayment->type_account,
                $methodPayment->entity ? $methodPayment->entity->name : 'N/A',
                $methodPayment->currency ? $methodPayment->currency->name : 'N/A',
                number_format($methodPayment->current_balance, 2, ',', '.'),
                number_format($methodPayment->initial_balance, 2, ',', '.')
            ];

            $totalBalance += $methodPayment->current_balance;
        }

        $this->table($headers, $rows);

        $this->newLine();
        $this->info("💵 Saldo Total General: " . number_format($totalBalance, 2, ',', '.'));

        // Mostrar estadísticas adicionales
        $this->newLine();
        $this->info('📈 Estadísticas:');
        $this->line("  • Cuentas con saldo positivo: " . $methodPayments->where('current_balance', '>', 0)->count());
        $this->line("  • Cuentas con saldo negativo: " . $methodPayments->where('current_balance', '<', 0)->count());
        $this->line("  • Cuentas con saldo cero: " . $methodPayments->where('current_balance', '=', 0)->count());

        // Mostrar movimientos recientes si se solicita
        if ($this->confirm('¿Deseas ver los movimientos recientes?')) {
            $this->showRecentMovements();
        }
    }

    /**
     * Mostrar movimientos recientes
     */
    private function showRecentMovements()
    {
        $this->newLine();
        $this->info('🔄 Movimientos Recientes (Últimos 10)');
        $this->line('=====================================');

        // Obtener movimientos recientes de ambos tipos
        $businessMovements = BussinesMovement::with(['methodPayment', 'currency'])
            ->orderBy('date', 'desc')
            ->limit(5)
            ->get();

        $eventMovements = EventMovement::with(['methodPayment', 'currency'])
            ->where('status', 'Activo')
            ->orderBy('date', 'desc')
            ->limit(5)
            ->get();

        $allMovements = $businessMovements->concat($eventMovements)
            ->sortByDesc('date')
            ->take(10);

        if ($allMovements->isEmpty()) {
            $this->warn('No hay movimientos recientes.');
            return;
        }

        $headers = ['Fecha', 'Tipo', 'Origen', 'Monto', 'Cuenta', 'Moneda'];
        $rows = [];

        foreach ($allMovements as $movement) {
            $rows[] = [
                $movement->date->format('d/m/Y'),
                $movement->type,
                $movement instanceof BussinesMovement ? 'Negocio' : 'Evento',
                number_format($movement->amount, 2, ',', '.'),
                $movement->methodPayment ? $movement->methodPayment->account_holder : 'N/A',
                $movement->currency ? $movement->currency->name : 'N/A'
            ];
        }

        $this->table($headers, $rows);
    }
}
