<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('accidents', function (Blueprint $table) {
            $table->id('accident_id');
            $table->string('case_number')->unique();
            $table->unsignedBigInteger('type_id');
            $table->timestamp('accident_date')->nullable();
            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();
            $table->string('address')->nullable();
            $table->text('description')->nullable();
            $table->string('severity')->default('Minor');
            $table->string('reported_by')->nullable();
            $table->timestamps();

            $table->foreign('type_id')->references('id')->on('vehicle_types')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accidents');
    }
};
