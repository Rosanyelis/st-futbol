<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AccountPayablePayment;
use App\Models\AccountPayable;
use Illuminate\Support\Facades\DB;

class CleanDuplicatePayablePayments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payments:clean-duplicate-payables {account_id? : ID de la cuenta por pagar específica} {--dry-run : Solo mostrar qué se eliminaría sin hacer cambios}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Identifica y elimina pagos duplicados en cuentas por pagar';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $accountId = $this->argument('account_id');
        $dryRun = $this->option('dry-run');

        if ($accountId) {
            $this->cleanSpecificAccount($accountId, $dryRun);
        } else {
            $this->cleanAllAccounts($dryRun);
        }
    }

    /**
     * Limpiar pagos duplicados de una cuenta específica
     */
    private function cleanSpecificAccount($accountId, $dryRun)
    {
        $this->info("Analizando cuenta por pagar ID: {$accountId}");

        $accountPayable = AccountPayable::find($accountId);
        if (!$accountPayable) {
            $this->error("No se encontró la cuenta por pagar con ID: {$accountId}");
            return;
        }

        $this->info("Cuenta por pagar: {$accountPayable->supplier->name} - Evento: {$accountPayable->event->name}");

        $payments = $accountPayable->payments()->orderBy('id')->get();
        $this->info("Total de pagos encontrados: " . $payments->count());

        $seenPayments = [];
        $paymentsToDelete = [];

        foreach ($payments as $payment) {
            $key = $payment->amount . '_' . $payment->date->format('Y-m-d');
            
            if (in_array($key, $seenPayments)) {
                $paymentsToDelete[] = $payment;
                $this->warn("Pago duplicado encontrado - ID: {$payment->id}, Monto: {$payment->amount}, Fecha: {$payment->date->format('Y-m-d')}");
            } else {
                $seenPayments[] = $key;
                $this->line("Pago único - ID: {$payment->id}, Monto: {$payment->amount}, Fecha: {$payment->date->format('Y-m-d')}");
            }
        }

        if (empty($paymentsToDelete)) {
            $this->info("No se encontraron pagos duplicados en esta cuenta.");
            return;
        }

        $this->warn("Pagos duplicados a eliminar: " . count($paymentsToDelete));

        if ($dryRun) {
            $this->info("MODO DRY-RUN: No se eliminarán registros.");
            return;
        }

        if ($this->confirm('¿Desea eliminar estos pagos duplicados?')) {
            $deletedCount = 0;
            foreach ($paymentsToDelete as $payment) {
                $payment->delete();
                $deletedCount++;
                $this->line("Eliminado pago ID: {$payment->id}");
            }
            $this->info("Se eliminaron {$deletedCount} pagos duplicados.");
        } else {
            $this->info("Operación cancelada.");
        }
    }

    /**
     * Limpiar pagos duplicados de todas las cuentas
     */
    private function cleanAllAccounts($dryRun)
    {
        $this->info("Analizando todas las cuentas por pagar...");

        $accountPayables = AccountPayable::with('payments')->get();
        $totalDuplicates = 0;
        $accountsWithDuplicates = 0;

        foreach ($accountPayables as $accountPayable) {
            $payments = $accountPayable->payments()->orderBy('id')->get();
            
            if ($payments->count() <= 1) {
                continue; // No puede haber duplicados con 1 o menos pagos
            }

            $seenPayments = [];
            $duplicates = [];

            foreach ($payments as $payment) {
                $key = $payment->amount . '_' . $payment->date->format('Y-m-d');
                
                if (in_array($key, $seenPayments)) {
                    $duplicates[] = $payment;
                } else {
                    $seenPayments[] = $key;
                }
            }

            if (!empty($duplicates)) {
                $accountsWithDuplicates++;
                $totalDuplicates += count($duplicates);
                
                $this->warn("Cuenta ID {$accountPayable->id} ({$accountPayable->supplier->name}): " . count($duplicates) . " pagos duplicados");
            }
        }

        if ($totalDuplicates === 0) {
            $this->info("No se encontraron pagos duplicados en ninguna cuenta.");
            return;
        }

        $this->warn("Resumen:");
        $this->warn("- Cuentas con duplicados: {$accountsWithDuplicates}");
        $this->warn("- Total de pagos duplicados: {$totalDuplicates}");

        if ($dryRun) {
            $this->info("MODO DRY-RUN: No se eliminarán registros.");
            return;
        }

        if ($this->confirm('¿Desea eliminar todos los pagos duplicados?')) {
            $deletedCount = 0;
            
            foreach ($accountPayables as $accountPayable) {
                $payments = $accountPayable->payments()->orderBy('id')->get();
                
                if ($payments->count() <= 1) {
                    continue;
                }

                $seenPayments = [];
                $duplicates = [];

                foreach ($payments as $payment) {
                    $key = $payment->amount . '_' . $payment->date->format('Y-m-d');
                    
                    if (in_array($key, $seenPayments)) {
                        $duplicates[] = $payment;
                    } else {
                        $seenPayments[] = $key;
                    }
                }

                foreach ($duplicates as $payment) {
                    $payment->delete();
                    $deletedCount++;
                }
            }
            
            $this->info("Se eliminaron {$deletedCount} pagos duplicados de {$accountsWithDuplicates} cuentas.");
        } else {
            $this->info("Operación cancelada.");
        }
    }
}