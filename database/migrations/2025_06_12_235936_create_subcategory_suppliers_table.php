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
        
        Schema::create('subcategory_suppliers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_supplier_id')->constrained('category_suppliers')->onUpdate('cascade')->onDelete('cascade');
            $table->string('name');
            $table->timestamps();
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subcategory_suppliers');
    }
};
