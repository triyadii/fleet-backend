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
        Schema::table('tracking_logs', function (Blueprint $table) {
            $table->boolean('fokus')->default(false);
            $table->boolean('mengantuk')->default(false);
            $table->boolean('berisik')->default(false);
            $table->boolean('tidak_ditempat')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tracking_logs', function (Blueprint $table) {
            $table->dropColumn(['fokus', 'mengantuk', 'berisik', 'tidak_ditempat']);
        });
    }
};
