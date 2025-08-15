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
        Schema::create('club_event_players', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_club_id')->constrained('event_clubs')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('club_id')->constrained('clubs')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onUpdate('cascade')->onDelete('cascade');
            $table->boolean('has_accommodation')->default(false);
            $table->integer('players_quantity')->nullable();
            $table->decimal('player_price', 10, 2)->nullable();
            $table->integer('total_players')->nullable();
            $table->integer('teachers_quantity')->nullable();
            $table->decimal('teacher_price', 10, 2)->nullable();
            $table->integer('total_teachers')->nullable();
            $table->integer('companions_quantity')->nullable();
            $table->decimal('companion_price', 10, 2)->nullable();
            $table->integer('total_companions')->nullable();
            $table->integer('drivers_quantity')->nullable();
            $table->decimal('driver_price', 10, 2)->nullable();
            $table->integer('total_drivers')->nullable();
            $table->integer('liberated_quantity')->nullable();
            $table->integer('total_people')->nullable();
            $table->decimal('total_amount', 10, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('club_event_players');
    }
};
