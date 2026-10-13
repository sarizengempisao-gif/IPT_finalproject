<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id('booking_id');
            $table->foreignId('passenger_id')->constrained('passengers', 'passenger_id')->restrictOnDelete();
            $table->foreignId('driver_id')->constrained('drivers', 'driver_id')->restrictOnDelete();
            $table->foreignId('vehicle_id')->constrained('vehicles', 'vehicle_id')->restrictOnDelete();
            $table->string('booking_reference', 50)->unique();
            $table->string('pickup_location');
            $table->string('dropoff_location');
            $table->date('booking_date');
            $table->time('booking_time');
            $table->decimal('fare', 10, 2);
            $table->string('status')->default('Pending');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
