<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('history_change_currencies', function (Blueprint $table) {
            $table->enum('type_operation', ['Multiplicacion', 'Division'])
                  ->default('Multiplicacion')
                  ->after('exchange_rate')
                  ->comment('Tipo de operación para el cálculo del cambio de moneda');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('history_change_currencies', function (Blueprint $table) {
            $table->dropColumn('type_operation');
        });
    }
};
