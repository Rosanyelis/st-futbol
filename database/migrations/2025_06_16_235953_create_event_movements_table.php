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
            $table->foreignId('bussines_id')->nullable()->constrained('bussines')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('event_id')->nullable()->constrained('events')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('club_id')->nullable()->constrained('clubs')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('method_payment_id')->nullable()->constrained('method_payments')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('category_income_id')->nullable()->constrained('category_incomes')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('subcategory_expense_id')->nullable()->constrained('subcategory_expenses')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('category_egress_id')->nullable()->constrained('category_egresses')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('currency_id')->nullable()->constrained('currencies')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onUpdate('cascade')->onDelete('cascade');
            $table->decimal('amount', 18, 2);
            $table->date('date');
            $table->text('description')->nullable();
            $table->enum('type', ["Ingreso","Egreso"]);
            $table->timestamps();
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_movements');
    }
};
