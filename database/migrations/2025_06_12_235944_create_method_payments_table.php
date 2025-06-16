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
    
        Schema::create('method_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_method_payment_id')->constrained('category_method_payments')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('entity_id')->constrained('entities')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('currency_id')->constrained('currencies')->onUpdate('cascade')->onDelete('cascade');
            $table->string('account_holder');
            $table->string('account_number')->nullable();
            $table->integer('cbu_cvu')->nullable();
            $table->string('alias')->nullable();
            $table->enum('type_account', ["Propia","Terceros"]);
            $table->decimal('initial_balance')->nullable();
            $table->decimal('current_balance')->nullable();
            $table->timestamps();
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('method_payments');
    }
};
