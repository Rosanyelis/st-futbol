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
       
        Schema::create('clubs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('currency_id')->constrained('currencies')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->onUpdate('cascade')->onDelete('cascade');
            $table->string('name');
            $table->string('logo');
            $table->string('cuit');
            $table->string('responsible');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->foreignId('country_id')->nullable()->constrained('countries')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('province_id')->nullable()->constrained('provinces')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('city_id')->nullable()->constrained('cities')->onUpdate('cascade')->onDelete('cascade');
            $table->boolean('has_accommodation')->default(false);
            $table->integer('players_quantity')->nullable();
            $table->decimal('player_price', 10, 2)->nullable()->default(0);
            $table->decimal('total_player', 10, 2)->nullable()->default(0);
            $table->integer('teachers_quantity')->nullable();
            $table->decimal('teacher_price', 10, 2)->nullable()->default(0);
            $table->decimal('total_teacher', 10, 2)->nullable()->default(0);
            $table->integer('companions_quantity')->nullable();
            $table->decimal('companion_price', 10, 2)->nullable()->default(0);
            $table->decimal('total_companion', 10, 2)->nullable()->default(0);
            $table->integer('drivers_quantity')->nullable();
            $table->decimal('driver_price', 10, 2)->nullable()->default(0);
            $table->decimal('total_driver', 10, 2)->nullable()->default(0);
            $table->integer('liberated_quantity')->nullable();
            $table->integer('total_people')->nullable();
            $table->decimal('total_amount', 10, 2)->nullable()->default(0);
            $table->timestamps();
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clubs');
    }
};
