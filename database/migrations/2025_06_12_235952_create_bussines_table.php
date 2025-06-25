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
        Schema::disableForeignKeyConstraints();

        Schema::create('bussines', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->foreignId('country_id')->nullable()->constrained('Country')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('province_id')->nullable()->constrained('Province')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('city_id')->nullable()->constrained('City')->onUpdate('cascade')->onDelete('cascade');
            $table->string('logo')->nullable();
            $table->string('cuit')->nullable();
            $table->timestamps();
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bussines');
    }
};
