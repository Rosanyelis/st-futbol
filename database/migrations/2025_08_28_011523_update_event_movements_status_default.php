<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Primero, actualizar todos los registros existentes que tengan status NULL a 'Activo'
        DB::table('event_movements')
            ->whereNull('status')
            ->update(['status' => 'Activo']);
            
        // Luego, modificar la columna para establecer el valor por defecto
        Schema::table('event_movements', function (Blueprint $table) {
            $table->string('status')->default('Activo')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('event_movements', function (Blueprint $table) {
            $table->string('status')->nullable()->change();
        });
    }
};
