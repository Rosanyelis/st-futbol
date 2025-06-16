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
        Schema::table('clubs', function (Blueprint $table) {
            $table->integer('jugadores')->nullable()->after('has_accommodation');
            $table->decimal('precio_jugador', 10, 2)->nullable()->default(0)->after('jugadores');
            $table->decimal('total_jugador', 10, 2)->nullable()->default(0)->after('precio_jugador');
            $table->integer('profesores')->nullable()->after('total_jugador');
            $table->decimal('precio_profesor', 10, 2)->nullable()->default(0)->after('profesores');
            $table->decimal('total_profesor', 10, 2)->nullable()->default(0)->after('precio_profesor');
            $table->integer('acompañantes')->nullable()->after('total_profesor');
            $table->decimal('precio_acompañante', 10, 2)->nullable()->default(0)->after('acompañantes');
            $table->decimal('total_acompañante', 10, 2)->nullable()->default(0)->after('precio_acompañante');
            $table->integer('choferes')->nullable()->after('total_acompañante');
            $table->decimal('precio_chofer', 10, 2)->nullable()->default(0)->after('choferes');
            $table->decimal('total_chofer', 10, 2)->nullable()->default(0)->after('precio_chofer');
            $table->integer('liberados')->nullable()->after('total_chofer');
            $table->decimal('total_delegacion', 10, 2)->nullable()->default(0)->after('liberados');
            $table->decimal('total_dolares', 10, 2)->nullable()->default(0)->after('total_delegacion');
            $table->decimal('total_amount', 10, 2)->nullable()->default(0)->after('total_dolares'); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clubs', function (Blueprint $table) {
            $table->dropColumn([
                'jugadores',
                'precio_jugador',
                'total_jugador',
                'profesores',
                'precio_profesor',
                'total_profesor',
                'acompañantes',
                'precio_acompañante',
                'total_acompañante',
                'choferes',
                'precio_chofer',
                'total_chofer',
                'liberados',
                'total_delegacion',
                'total_dolares',
                'total_amount'
            ]);
        });
    }
};
