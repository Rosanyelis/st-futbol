<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AccountReceivable;
use App\Models\AccountPayable;

class UpdateAccountStatuses extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'accounts:update-statuses';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Actualiza los status de todas las cuentas por cobrar y por pagar basado en sus pagos';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Iniciando actualización de status de cuentas...');

        // Actualizar cuentas por cobrar
        $this->info('Actualizando cuentas por cobrar...');
        $receivables = AccountReceivable::all();
        $receivablesUpdated = 0;

        foreach ($receivables as $receivable) {
            $oldStatus = $receivable->status;
            $receivable->updateStatusAfterPayment();
            
            if ($oldStatus !== $receivable->status) {
                $receivablesUpdated++;
                $this->line("Cuenta por cobrar #{$receivable->id}: {$oldStatus} → {$receivable->status}");
            }
        }

        $this->info("Cuentas por cobrar actualizadas: {$receivablesUpdated}");

        // Actualizar cuentas por pagar
        $this->info('Actualizando cuentas por pagar...');
        $payables = AccountPayable::all();
        $payablesUpdated = 0;

        foreach ($payables as $payable) {
            $oldStatus = $payable->status;
            $payable->updateStatusAfterPayment();
            
            if ($oldStatus !== $payable->status) {
                $payablesUpdated++;
                $this->line("Cuenta por pagar #{$payable->id}: {$oldStatus} → {$payable->status}");
            }
        }

        $this->info("Cuentas por pagar actualizadas: {$payablesUpdated}");

        $this->info('¡Actualización completada!');
        
        return 0;
    }
}
