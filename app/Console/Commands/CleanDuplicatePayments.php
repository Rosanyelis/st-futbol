<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AccountReceivable;
use App\Models\AccountReceivablePayment;

class CleanDuplicatePayments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payments:clean-duplicates {--account-id= : ID específico de cuenta por cobrar}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Eliminar pagos duplicados de las cuentas por cobrar';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $accountId = $this->option('account-id');
        
        if ($accountId) {
            $accounts = AccountReceivable::where('id', $accountId)->get();
        } else {
            $accounts = AccountReceivable::all();
        }

        $totalDeleted = 0;

        foreach ($accounts as $account) {
            $this->info("Procesando cuenta por cobrar ID: {$account->id}");
            
            $payments = $account->payments()->orderBy('id')->get();
            $this->info("Pagos antes de limpiar: {$payments->count()}");
            
            $seen = [];
            $toDelete = [];

            foreach ($payments as $payment) {
                $key = $payment->amount . '_' . $payment->date;
                if (in_array($key, $seen)) {
                    $toDelete[] = $payment->id;
                    $this->warn("Pago duplicado encontrado - ID: {$payment->id}, Monto: {$payment->amount}, Fecha: {$payment->date}");
                } else {
                    $seen[] = $key;
                }
            }

            if (count($toDelete) > 0) {
                $this->info("Eliminando " . count($toDelete) . " pagos duplicados...");
                
                foreach ($toDelete as $id) {
                    AccountReceivablePayment::find($id)->delete();
                    $totalDeleted++;
                }
                
                $this->info("Pagos después de limpiar: {$account->fresh()->payments->count()}");
                $this->info("Total pagado: {$account->fresh()->payments->sum('amount')}");
            } else {
                $this->info("No se encontraron pagos duplicados en esta cuenta.");
            }
            
            $this->line('---');
        }

        $this->info("Total de pagos duplicados eliminados: {$totalDeleted}");
    }
}