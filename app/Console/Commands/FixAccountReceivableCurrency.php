<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AccountReceivable;
use App\Models\EventMovement;

class FixAccountReceivableCurrency extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:account-receivable-currency {--account-id= : ID específico de cuenta por cobrar} {--dry-run : Solo mostrar qué se corregiría sin hacer cambios}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Corregir la moneda de las cuentas por cobrar basándose en los métodos de pago utilizados';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $accountId = $this->option('account-id');
        $isDryRun = $this->option('dry-run');
        
        if ($isDryRun) {
            $this->info('MODO DRY-RUN: Solo se mostrarán los cambios que se harían');
        }

        if ($accountId) {
            $accounts = AccountReceivable::where('id', $accountId)->get();
        } else {
            $accounts = AccountReceivable::all();
        }

        $fixedCount = 0;

        foreach ($accounts as $account) {
            $this->info("Procesando cuenta por cobrar ID: {$account->id}");
            $this->info("  - Moneda actual: {$account->currency_id} (" . $account->currency->name . ")");
            
            // Obtener todos los movimientos de evento relacionados
            $movements = EventMovement::where('account_receivable_id', $account->id)
                ->whereNotNull('method_payment_id')
                ->with('methodPayment.currency')
                ->get();
            
            if ($movements->isEmpty()) {
                $this->info("  - No hay movimientos con método de pago");
                continue;
            }
            
            // Obtener la moneda más común usada en los pagos
            $currencyCounts = [];
            foreach ($movements as $movement) {
                if ($movement->methodPayment && $movement->methodPayment->currency) {
                    $currencyId = $movement->methodPayment->currency_id;
                    $currencyCounts[$currencyId] = ($currencyCounts[$currencyId] ?? 0) + 1;
                }
            }
            
            if (empty($currencyCounts)) {
                $this->info("  - No se encontraron métodos de pago válidos");
                continue;
            }
            
            // Obtener la moneda más usada
            $mostUsedCurrencyId = array_keys($currencyCounts, max($currencyCounts))[0];
            $mostUsedCurrency = \App\Models\Currency::find($mostUsedCurrencyId);
            
            $this->info("  - Moneda más usada en pagos: {$mostUsedCurrencyId} (" . $mostUsedCurrency->name . ")");
            $this->info("  - Frecuencia: " . max($currencyCounts) . " de " . $movements->count() . " movimientos");
            
            // Verificar si necesita corrección
            if ($account->currency_id !== $mostUsedCurrencyId) {
                $this->warn("  - INCONSISTENCIA: La cuenta usa {$account->currency_id} pero los pagos usan {$mostUsedCurrencyId}");
                
                if (!$isDryRun) {
                    $account->update(['currency_id' => $mostUsedCurrencyId]);
                    $this->info("  ✅ Corregido a moneda: {$mostUsedCurrencyId}");
                } else {
                    $this->info("  🔍 Se corregiría a moneda: {$mostUsedCurrencyId}");
                }
                
                $fixedCount++;
            } else {
                $this->info("  ✅ Moneda correcta");
            }
            
            $this->line('---');
        }

        if ($isDryRun) {
            $this->info("\nResumen DRY-RUN:");
            $this->info("Cuentas que se corregirían: {$fixedCount}");
            $this->info("\nPara aplicar los cambios, ejecute el comando sin --dry-run");
        } else {
            $this->info("\nResumen:");
            $this->info("Cuentas corregidas: {$fixedCount}");
        }
    }
}