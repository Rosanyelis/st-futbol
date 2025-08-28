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
        Schema::table('bussines_movements', function (Blueprint $table) {
            $table->foreignId('club_id')->nullable()->constrained('clubs')->onUpdate('cascade')->onDelete('cascade')->after('bussines_id');
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->onUpdate('cascade')->onDelete('cascade')->after('club_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bussines_movements', function (Blueprint $table) {
            $table->dropForeign(['club_id']);
            $table->dropForeign(['supplier_id']);
            $table->dropColumn(['club_id', 'supplier_id']);
        });
    }
};
