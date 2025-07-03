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
        Schema::create('history_change_currencies', function (Blueprint $table) {
            $table->id(); 
            $table->foreignId('bussines_id')->nullable()->constrained('bussines')->onDelete('cascade');
            $table->foreignId('event_id')->nullable()->constrained('events')->onDelete('cascade');
            $table->foreignId('currency_id')->nullable()->constrained('currencies')->onDelete('cascade');
            $table->foreignId('method_payment_id')->nullable()->constrained('method_payments')->onDelete('cascade');
            $table->date('date');
            $table->decimal('amount', 18, 2); // monto a cambiar en la moneda original
            $table->foreignId('method_payment_receptor_id')->nullable()->constrained('method_payments')->onDelete('cascade');
            $table->foreignId('currency_receptor_id')->nullable()->constrained('currencies')->onDelete('cascade');
            $table->decimal('exchange_rate', 18, 2)->nullable(); // tasa de cambio aplicada
            $table->decimal('amount_converted', 18, 2)->nullable(); // monto recibido en la moneda de destino
            $table->string('description')->nullable(); // descripción opcional del cambio
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('history_change_currencies');
    }
};
