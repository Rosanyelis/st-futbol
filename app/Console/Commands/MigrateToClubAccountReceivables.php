<?php

namespace App\Console\Commands;

use App\Models\Club;
use App\Models\Event;
use App\Models\ClubPayment;
use App\Models\ClubAccountReceivable;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MigrateToClubAccountReceivables extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:club-account-receivables';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrar datos existentes a la nueva estructura de cuentas por cobrar';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Iniciando migración a la nueva estructura de cuentas por cobrar...');

        // Verificar que las tablas existen
        if (!DB::getSchemaBuilder()->hasTable('club_account_receivables')) {
            $this->error('La tabla club_account_receivables no existe. Ejecute las migraciones primero.');
            return 1;
        }

        // Obtener todos los clubs que tienen total_amount
        $clubs = Club::whereNotNull('total_amount')
                    ->where('total_amount', '>', 0)
                    ->get();

        if ($clubs->isEmpty()) {
            $this->info('No hay clubs con montos para migrar.');
            return 0;
        }

        $this->info("Encontrados {$clubs->count()} clubs para migrar.");
        
        $migratedCount = 0;
        $errors = [];
        
        $progressBar = $this->output->createProgressBar($clubs->count());
        $progressBar->start();
        
        foreach ($clubs as $club) {
            try {
                // Obtener el evento asociado al club (si existe)
                $event = null;
                if ($club->event_id) {
                    $event = Event::find($club->event_id);
                }

                // Si no hay evento, crear uno por defecto o usar el más reciente
                if (!$event) {
                    $event = Event::orderBy('created_at', 'desc')->first();
                    if (!$event) {
                        $this->warn("No se encontró evento para el club {$club->name}");
                        $progressBar->advance();
                        continue;
                    }
                }

                // Calcular el monto pagado hasta ahora
                $paidAmount = ClubPayment::where('club_id', $club->id)->sum('amount');
                $pendingAmount = $club->total_amount - $paidAmount;

                // Crear la cuenta por cobrar
                $receivable = ClubAccountReceivable::create([
                    'club_id' => $club->id,
                    'event_id' => $event->id,
                    'currency_id' => $club->currency_id,
                    'total_amount' => $club->total_amount,
                    'paid_amount' => $paidAmount,
                    'pending_amount' => $pendingAmount,
                    'due_date' => Carbon::now()->addDays(30)->toDateString(),
                    'created_date' => Carbon::now()->toDateString(),
                    'status' => $pendingAmount <= 0 ? 'Pagado' : ($paidAmount > 0 ? 'Parcial' : 'Pendiente'),
                    'notes' => "Migrado automáticamente desde club existente",
                ]);

                // Actualizar los pagos existentes para relacionarlos con la cuenta por cobrar
                ClubPayment::where('club_id', $club->id)
                    ->update(['club_account_receivable_id' => $receivable->id]);

                $migratedCount++;

            } catch (\Exception $e) {
                $errors[] = "Error migrando club {$club->name}: " . $e->getMessage();
            }
            
            $progressBar->advance();
        }
        
        $progressBar->finish();
        $this->newLine();

        $this->info("Migración completada. {$migratedCount} cuentas por cobrar creadas exitosamente.");

        if (!empty($errors)) {
            $this->warn("Errores encontrados:");
            foreach ($errors as $error) {
                $this->line("  - {$error}");
            }
        }

        return 0;
    }
} 