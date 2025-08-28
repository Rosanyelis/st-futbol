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
        Schema::create('account_receivables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('club_id')->constrained('clubs')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('event_id')->constrained('events')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('currency_id')->constrained('currencies')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('supplier_id')->constrained('suppliers')->onUpdate('cascade')->onDelete('cascade');
            $table->date('date');
            $table->boolean('has_accommodation')->default(false);
            $table->integer('players_quantity')->nullable();
            $table->decimal('player_price', 10, 2)->default(0.00);
            $table->decimal('total_players', 10, 2)->default(0.00);
            $table->integer('teachers_quantity')->nullable();
            $table->decimal('teacher_price', 10, 2)->default(0.00);
            $table->decimal('total_teachers', 10, 2)->default(0.00);
            $table->integer('companions_quantity')->nullable();
            $table->decimal('companion_price', 10, 2)->default(0.00);
            $table->decimal('total_companions', 10, 2)->default(0.00);
            $table->integer('drivers_quantity')->nullable();
            $table->decimal('driver_price', 10, 2)->default(0.00);
            $table->decimal('total_drivers', 10, 2)->default(0.00);
            $table->integer('liberated_quantity')->nullable();
            $table->integer('total_people')->nullable();
            $table->decimal('total_amount', 10, 2)->default(0.00);
            $table->string('description')->nullable();
            $table->string('status')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('account_receivables');
    }
};
