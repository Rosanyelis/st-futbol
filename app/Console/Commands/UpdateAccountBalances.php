<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\MethodPayment;
use App\Models\BussinesMovement;
use App\Models\EventMovement;
use Illuminate\Support\Facades\DB;

class UpdateAccountBalances extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'accounts:update-balances {--force : Force update without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Actualiza los saldos de las cuentas/métodos de pago basándose en los movimientos de BussinesMovement y EventMovement';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔄 Iniciando actualización de saldos de cuentas...');
        
        if (!$this->option('force')) {
            if (!$this->confirm('¿Estás seguro de que quieres actualizar todos los saldos de las cuentas?')) {
                $this->info('Operación cancelada.');
                return;
            }
        }

        try {
            DB::beginTransaction();

            // Obtener todos los métodos de pago
            $methodPayments = MethodPayment::all();
            $this->info("📊 Procesando {$methodPayments->count()} métodos de pago...");

            $updatedCount = 0;
            $bar = $this->output->createProgressBar($methodPayments->count());

            foreach ($methodPayments as $methodPayment) {
                $this->updateMethodPaymentBalance($methodPayment);
                $updatedCount++;
                $bar->advance();
            }

            $bar->finish();
            $this->newLine();

            DB::commit();

            $this->info("✅ Actualización completada exitosamente!");
            $this->info("📈 Se actualizaron {$updatedCount} métodos de pago");

        } catch (\Exception $e) {
            DB::rollback();
            $this->error("❌ Error durante la actualización: " . $e->getMessage());
            $this->error("🔄 Se revirtieron todos los cambios");
            return 1;
        }

        return 0;
    }

    /**
     * Actualiza el saldo de un método de pago específico
     */
    private function updateMethodPaymentBalance(MethodPayment $methodPayment)
    {
        // Calcular ingresos y egresos de BussinesMovement
        $businessIngresos = BussinesMovement::where('method_payment_id', $methodPayment->id)
            ->where('type', 'Ingreso')
            ->where('status', 'Activo')
            ->sum('amount');

        $businessEgresos = BussinesMovement::where('method_payment_id', $methodPayment->id)
            ->where('type', 'Egreso')
            ->where('status', 'Activo')
            ->sum('amount');

        // Calcular ingresos y egresos de EventMovement
        $eventIngresos = EventMovement::where('method_payment_id', $methodPayment->id)
            ->where('type', 'Ingreso')
            ->where('status', 'Activo')
            ->sum('amount');

        $eventEgresos = EventMovement::where('method_payment_id', $methodPayment->id)
            ->where('type', 'Egreso')
            ->where('status', 'Activo')
            ->sum('amount');

        // Calcular saldo total
        $totalIngresos = $businessIngresos + $eventIngresos;
        $totalEgresos = $businessEgresos + $eventEgresos;
        $saldoActual = $totalIngresos - $totalEgresos;

        // Actualizar el saldo en el método de pago
        $methodPayment->update([
            'current_balance' => $saldoActual
        ]);

        // Log detallado para debugging
        if ($this->option('verbose')) {
            $this->line("  💳 {$methodPayment->account_holder} ({$methodPayment->type_account}):");
            $this->line("    📈 Ingresos Negocio: " . number_format($businessIngresos, 2, ',', '.'));
            $this->line("    📉 Egresos Negocio: " . number_format($businessEgresos, 2, ',', '.'));
            $this->line("    📈 Ingresos Eventos: " . number_format($eventIngresos, 2, ',', '.'));
            $this->line("    📉 Egresos Eventos: " . number_format($eventEgresos, 2, ',', '.'));
            $this->line("    💰 Saldo Total: " . number_format($saldoActual, 2, ',', '.'));
            $this->newLine();
        }
    }
}
