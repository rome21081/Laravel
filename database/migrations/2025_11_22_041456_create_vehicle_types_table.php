<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('vehicle_types', function (Blueprint $table) {
            $table->id();
            $table->string('type_name');
            $table->timestamps();
        });
       
        DB::table('vehicle_types')->insert([
            ['type_name' => 'Car'],
            ['type_name' => 'Motorcycle'],
            ['type_name' => 'Bus'],
            ['type_name' => 'Truck'],
            ['type_name' => 'Bicycle'],
            ['type_name' => 'Person'],
            ['type_name' => 'Other'],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicle_types');
    }
};
