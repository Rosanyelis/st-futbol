<?php

namespace App\Console\Commands;

use App\Models\Club;
use App\Models\Event;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MigrateEventClubData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:event-club-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrar datos existentes de la relación event-club a la nueva tabla pivot';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Iniciando migración de datos event-club...');

        // Verificar que la tabla pivot existe
        if (!DB::getSchemaBuilder()->hasTable('event_clubs')) {
            $this->error('La tabla event_clubs no existe. Ejecute las migraciones primero.');
            return 1;
        }

        // Obtener todos los clubs que tienen event_id
        $clubs = Club::whereNotNull('event_id')->get();
        
        if ($clubs->isEmpty()) {
            $this->info('No hay datos para migrar.');
            return 0;
        }

        $this->info("Encontrados {$clubs->count()} clubs para migrar.");
        
        $migratedCount = 0;
        $errors = [];
        
        $progressBar = $this->output->createProgressBar($clubs->count());
        $progressBar->start();
        
        foreach ($clubs as $club) {
            try {
                // Verificar que el evento existe
                $event = Event::find($club->event_id);
                if (!$event) {
                    $errors[] = "Evento con ID {$club->event_id} no encontrado para el club {$club->name}";
                    $progressBar->advance();
                    continue;
                }

                // Verificar si ya existe la relación
                $existingRelation = DB::table('event_clubs')
                    ->where('event_id', $club->event_id)
                    ->where('club_id', $club->id)
                    ->exists();

                if ($existingRelation) {
                    $this->warn("Relación ya existe para club {$club->name} y evento {$event->name}");
                    $progressBar->advance();
                    continue;
                }

                // Insertar en la tabla pivot
                DB::table('event_clubs')->insert([
                    'event_id' => $club->event_id,
                    'club_id' => $club->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $migratedCount++;

            } catch (\Exception $e) {
                $errors[] = "Error migrando club {$club->name}: " . $e->getMessage();
            }
            
            $progressBar->advance();
        }
        
        $progressBar->finish();
        $this->newLine();

        $this->info("Migración completada. {$migratedCount} relaciones migradas exitosamente.");

        if (!empty($errors)) {
            $this->warn("Errores encontrados:");
            foreach ($errors as $error) {
                $this->line("  - {$error}");
            }
        }

        return 0;
    }
} 