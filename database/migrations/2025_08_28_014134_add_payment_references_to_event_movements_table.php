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
        Schema::table('event_movements', function (Blueprint $table) {
            // Referencias a los pagos para mantener consistencia de datos
            $table->unsignedBigInteger('account_receivable_payment_id')->nullable()->after('account_receivable_id');
            $table->unsignedBigInteger('account_payable_payment_id')->nullable()->after('account_payable_id');
            
            // Índices para mejorar el rendimiento
            $table->index('account_receivable_payment_id');
            $table->index('account_payable_payment_id');
            
            // Claves foráneas
            $table->foreign('account_receivable_payment_id')->references('id')->on('account_receivable_payments')->onDelete('set null');
            $table->foreign('account_payable_payment_id')->references('id')->on('account_payable_payments')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('event_movements', function (Blueprint $table) {
            $table->dropForeign(['account_receivable_payment_id']);
            $table->dropForeign(['account_payable_payment_id']);
            $table->dropIndex(['account_receivable_payment_id']);
            $table->dropIndex(['account_payable_payment_id']);
            $table->dropColumn(['account_receivable_payment_id', 'account_payable_payment_id']);
        });
    }
};
