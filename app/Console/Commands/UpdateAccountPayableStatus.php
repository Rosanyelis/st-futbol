<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AccountPayable;

class UpdateAccountPayableStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payable:update-status {account_id? : ID de la cuenta por pagar específica}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Actualiza el status de las cuentas por pagar basado en los pagos realizados';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $accountId = $this->argument('account_id');

        if ($accountId) {
            $this->updateSpecificAccount($accountId);
        } else {
            $this->updateAllAccounts();
        }
    }

    /**
     * Actualizar una cuenta específica
     */
    private function updateSpecificAccount($accountId)
    {
        $this->info("Actualizando cuenta por pagar ID: {$accountId}");

        $accountPayable = AccountPayable::with('payments')->find($accountId);
        if (!$accountPayable) {
            $this->error("No se encontró la cuenta por pagar con ID: {$accountId}");
            return;
        }

        $this->info("Cuenta por pagar: {$accountPayable->supplier->name} - Evento: {$accountPayable->event->name}");
        $this->info("Monto total: {$accountPayable->amount}");
        $this->info("Total pagado: {$accountPayable->getPaidAmount()}");
        $this->info("Status actual: {$accountPayable->status}");

        // Forzar actualización del status
        $accountPayable->updateStatusAfterPayment();
        $accountPayable->refresh();

        $this->info("Status actualizado: {$accountPayable->status}");
    }

    /**
     * Actualizar todas las cuentas
     */
    private function updateAllAccounts()
    {
        $this->info("Actualizando todas las cuentas por pagar...");

        $accountPayables = AccountPayable::with('payments')->get();
        $updatedCount = 0;

        foreach ($accountPayables as $accountPayable) {
            $oldStatus = $accountPayable->status;
            
            // Forzar actualización del status
            $accountPayable->updateStatusAfterPayment();
            $accountPayable->refresh();
            
            if ($oldStatus !== $accountPayable->status) {
                $updatedCount++;
                $this->line("Cuenta ID {$accountPayable->id}: {$oldStatus} → {$accountPayable->status}");
            }
        }

        $this->info("Se actualizaron {$updatedCount} cuentas por pagar.");
    }
}