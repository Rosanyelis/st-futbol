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
            $table->foreignId('account_payable_id')->nullable()->constrained('account_payables')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('account_receivable_id')->nullable()->constrained('account_receivables')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('event_movements', function (Blueprint $table) {
            $table->dropForeign(['account_payable_id']);
            $table->dropForeign(['account_receivable_id']);
            $table->dropColumn(['account_payable_id', 'account_receivable_id']);
        });
    }
};
