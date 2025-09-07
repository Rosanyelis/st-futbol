<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\EventMovement;
use App\Models\AccountReceivable;
use App\Models\MethodPayment;

class FixEventMovementCurrencies extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:event-movement-currencies {--dry-run : Solo mostrar qué se corregiría sin hacer cambios}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Corregir las monedas incorrectas en los movimientos de evento';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        
        if ($isDryRun) {
            $this->info('MODO DRY-RUN: Solo se mostrarán los cambios que se harían');
        }

        $movements = EventMovement::whereNotNull('account_receivable_id')
            ->with(['accountReceivable', 'methodPayment'])
            ->get();

        $fixedCount = 0;
        $errorCount = 0;

        foreach ($movements as $movement) {
            $accountReceivable = $movement->accountReceivable;
            $methodPayment = $movement->methodPayment;
            
            if (!$accountReceivable) {
                $this->warn("Movimiento ID {$movement->id}: No se encontró la cuenta por cobrar");
                continue;
            }

            $correctCurrencyId = $accountReceivable->currency_id;
            
            // Si tiene método de pago, usar su moneda
            if ($methodPayment) {
                $correctCurrencyId = $methodPayment->currency_id;
                
            // Verificar que las monedas coincidan
            if ($accountReceivable->currency_id !== $methodPayment->currency_id) {
                $this->warn("Movimiento ID {$movement->id}: Inconsistencia de monedas - Cuenta: {$accountReceivable->currency_id}, Método: {$methodPayment->currency_id}");
                $this->info("  - Cuenta por cobrar: " . $accountReceivable->currency->name);
                $this->info("  - Método de pago: " . $methodPayment->currency->name);
                
                // En caso de inconsistencia, usar la moneda del método de pago (que es la que realmente se usó para pagar)
                $correctCurrencyId = $methodPayment->currency_id;
                $this->info("  - Usando moneda del método de pago: {$correctCurrencyId}");
                
                // Verificar si la moneda actual es incorrecta
                if ($movement->currency_id !== $correctCurrencyId) {
                    $this->info("  - Moneda actual en movimiento: {$movement->currency_id}");
                    
                    if (!$isDryRun) {
                        $movement->update(['currency_id' => $correctCurrencyId]);
                        $this->info("  ✅ Corregido");
                    } else {
                        $this->info("  🔍 Se corregiría");
                    }
                    
                    $fixedCount++;
                }
                continue;
            }
            }

            // Verificar si la moneda actual es incorrecta
            if ($movement->currency_id !== $correctCurrencyId) {
                $this->info("Movimiento ID {$movement->id}:");
                $this->info("  - Moneda actual: {$movement->currency_id}");
                $this->info("  - Moneda correcta: {$correctCurrencyId}");
                $this->info("  - Cuenta por cobrar: {$accountReceivable->id}");
                $this->info("  - Método de pago: " . ($methodPayment ? $methodPayment->id : 'N/A'));
                
                if (!$isDryRun) {
                    $movement->update(['currency_id' => $correctCurrencyId]);
                    $this->info("  ✅ Corregido");
                } else {
                    $this->info("  🔍 Se corregiría");
                }
                
                $fixedCount++;
            }
        }

        if ($isDryRun) {
            $this->info("\nResumen DRY-RUN:");
            $this->info("Movimientos que se corregirían: {$fixedCount}");
            $this->info("Errores encontrados: {$errorCount}");
            $this->info("\nPara aplicar los cambios, ejecute el comando sin --dry-run");
        } else {
            $this->info("\nResumen:");
            $this->info("Movimientos corregidos: {$fixedCount}");
            $this->info("Errores encontrados: {$errorCount}");
        }
    }
}