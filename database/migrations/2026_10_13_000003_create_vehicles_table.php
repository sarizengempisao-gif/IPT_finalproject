<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id('vehicle_id');
            $table->string('plate_number', 30)->unique();
            $table->string('vehicle_type');
            $table->string('model');
            $table->year('year');
            $table->string('color', 40);
            $table->unsignedTinyInteger('capacity')->default(4);
            $table->string('status')->default('Available');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
