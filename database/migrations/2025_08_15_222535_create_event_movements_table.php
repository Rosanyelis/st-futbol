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
        Schema::create('event_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bussines_id')->constrained('bussines')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('event_id')->nullable()->constrained('events')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('club_id')->nullable()->constrained('clubs')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('method_payment_id')->nullable()->constrained('method_payments')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('category_income_id')->nullable()->constrained('category_incomes')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('category_egress_id')->nullable()->constrained('category_egresses')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('currency_id')->constrained('currencies')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('expense_id')->nullable()->constrained('expenses')->onUpdate('cascade')->onDelete('cascade');
            $table->decimal('amount', 15, 2);
            $table->date('date');
            $table->text('description')->nullable();
            $table->enum('status', ['Activo', 'Cancelado'])->default('Activo');
            $table->enum('type', ['Ingreso', 'Egreso']);
            $table->foreignId('user_id')->constrained('users')->onUpdate('cascade')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_movements');
    }
};
