<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->uuid('uuid')->after('id')->unique()->nullable();
        });
        
        $vehicles = DB::table('vehicles')->get();
        foreach ($vehicles as $vehicle) {
            DB::table('vehicles')->where('id', $vehicle->id)->update(['uuid' => (string) Str::uuid()]);
        }
    }

    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });
    }
};
