<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\EventMovement;
use App\Models\AccountReceivablePayment;
use App\Models\AccountPayablePayment;

class CleanDuplicateMovements extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clean:duplicate-movements {--dry-run : Solo mostrar qué se eliminaría sin hacer cambios}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Eliminar movimientos duplicados y sus pagos asociados';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        
        if ($isDryRun) {
            $this->info('MODO DRY-RUN: Solo se mostrarán los cambios que se harían');
        }

        $deletedMovements = 0;
        $deletedPayments = 0;

        // Buscar movimientos duplicados basándose en características similares
        $movements = EventMovement::whereNotNull('account_receivable_id')
            ->orWhereNotNull('account_payable_id')
            ->orderBy('created_at')
            ->get();

        $processed = [];
        $duplicates = [];

        foreach ($movements as $movement) {
            // Crear una clave única basada en características del movimiento
            $key = $this->createMovementKey($movement);
            
            if (isset($processed[$key])) {
                // Es un duplicado
                $duplicates[] = $movement;
                $this->warn("Movimiento duplicado encontrado:");
                $this->info("  - ID: {$movement->id}");
                $this->info("  - Tipo: {$movement->type}");
                $this->info("  - Monto: {$movement->amount}");
                $this->info("  - Fecha: {$movement->date}");
                $this->info("  - Descripción: " . substr($movement->description, 0, 50) . "...");
                $this->info("  - Creado: {$movement->created_at}");
                
                if (!$isDryRun) {
                    // Eliminar pagos asociados primero
                    if ($movement->account_receivable_payment_id) {
                        $payment = AccountReceivablePayment::find($movement->account_receivable_payment_id);
                        if ($payment) {
                            $payment->delete();
                            $deletedPayments++;
                            $this->info("  ✅ Pago de cuenta por cobrar eliminado: {$payment->id}");
                        }
                    }
                    
                    if ($movement->account_payable_payment_id) {
                        $payment = AccountPayablePayment::find($movement->account_payable_payment_id);
                        if ($payment) {
                            $payment->delete();
                            $deletedPayments++;
                            $this->info("  ✅ Pago de cuenta por pagar eliminado: {$payment->id}");
                        }
                    }
                    
                    // Eliminar el movimiento
                    $movement->delete();
                    $deletedMovements++;
                    $this->info("  ✅ Movimiento eliminado: {$movement->id}");
                } else {
                    $this->info("  🔍 Se eliminaría el movimiento y sus pagos asociados");
                }
                
                $this->line('---');
            } else {
                $processed[$key] = $movement;
            }
        }

        if ($isDryRun) {
            $this->info("\nResumen DRY-RUN:");
            $this->info("Movimientos duplicados encontrados: " . count($duplicates));
            $this->info("Movimientos que se eliminarían: " . count($duplicates));
            $this->info("\nPara aplicar los cambios, ejecute el comando sin --dry-run");
        } else {
            $this->info("\nResumen:");
            $this->info("Movimientos duplicados eliminados: {$deletedMovements}");
            $this->info("Pagos asociados eliminados: {$deletedPayments}");
        }
    }

    /**
     * Crear una clave única para identificar movimientos duplicados
     */
    private function createMovementKey($movement)
    {
        return sprintf(
            '%s_%s_%s_%s_%s_%s_%s',
            $movement->type,
            $movement->amount,
            $movement->date,
            $movement->account_receivable_id ?? 'null',
            $movement->account_payable_id ?? 'null',
            $movement->method_payment_id ?? 'null',
            substr($movement->description, 0, 50) // Solo los primeros 50 caracteres
        );
    }
}