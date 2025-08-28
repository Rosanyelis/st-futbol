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
        // Agregar campo description a account_receivable_payments
        Schema::table('account_receivable_payments', function (Blueprint $table) {
            $table->text('description')->nullable()->after('amount');
        });

        // Agregar campo description a account_payable_payments
        Schema::table('account_payable_payments', function (Blueprint $table) {
            $table->text('description')->nullable()->after('amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remover campo description de account_receivable_payments
        Schema::table('account_receivable_payments', function (Blueprint $table) {
            $table->dropColumn('description');
        });

        // Remover campo description de account_payable_payments
        Schema::table('account_payable_payments', function (Blueprint $table) {
            $table->dropColumn('description');
        });
    }
};
