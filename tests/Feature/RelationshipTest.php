<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Driver;
use App\Models\Passenger;
use App\Models\Payment;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RelationshipTest extends TestCase
{
    use RefreshDatabase;

    public function test_passenger_has_many_bookings(): void
    {
        $passenger = Passenger::factory()->create();
        $driver = Driver::factory()->create(['status' => 'Available']);
        $vehicle = Vehicle::factory()->create(['status' => 'Available']);

        Booking::factory(2)->create([
            'passenger_id' => $passenger->passenger_id,
            'driver_id' => $driver->driver_id,
            'vehicle_id' => $vehicle->vehicle_id,
        ]);

        $this->assertCount(2, $passenger->bookings);
        $this->assertInstanceOf(Booking::class, $passenger->bookings->first());
    }

    public function test_driver_has_many_bookings(): void
    {
        $passenger = Passenger::factory()->create();
        $driver = Driver::factory()->create(['status' => 'Available']);
        $vehicle = Vehicle::factory()->create(['status' => 'Available']);

        Booking::factory(2)->create([
            'passenger_id' => $passenger->passenger_id,
            'driver_id' => $driver->driver_id,
            'vehicle_id' => $vehicle->vehicle_id,
        ]);

        $this->assertCount(2, $driver->bookings);
    }

    public function test_vehicle_has_many_bookings(): void
    {
        $passenger = Passenger::factory()->create();
        $driver = Driver::factory()->create(['status' => 'Available']);
        $vehicle = Vehicle::factory()->create(['status' => 'Available']);

        Booking::factory(2)->create([
            'passenger_id' => $passenger->passenger_id,
            'driver_id' => $driver->driver_id,
            'vehicle_id' => $vehicle->vehicle_id,
        ]);

        $this->assertCount(2, $vehicle->bookings);
    }

    public function test_booking_belongs_to_passenger_driver_vehicle(): void
    {
        $passenger = Passenger::factory()->create();
        $driver = Driver::factory()->create(['status' => 'Available']);
        $vehicle = Vehicle::factory()->create(['status' => 'Available']);

        $booking = Booking::factory()->create([
            'passenger_id' => $passenger->passenger_id,
            'driver_id' => $driver->driver_id,
            'vehicle_id' => $vehicle->vehicle_id,
        ]);

        $this->assertInstanceOf(Passenger::class, $booking->passenger);
        $this->assertInstanceOf(Driver::class, $booking->driver);
        $this->assertInstanceOf(Vehicle::class, $booking->vehicle);
        $this->assertEquals($passenger->passenger_id, $booking->passenger->passenger_id);
        $this->assertEquals($driver->driver_id, $booking->driver->driver_id);
        $this->assertEquals($vehicle->vehicle_id, $booking->vehicle->vehicle_id);
    }

    public function test_booking_has_many_payments(): void
    {
        $passenger = Passenger::factory()->create();
        $driver = Driver::factory()->create(['status' => 'Available']);
        $vehicle = Vehicle::factory()->create(['status' => 'Available']);

        $booking = Booking::factory()->create([
            'passenger_id' => $passenger->passenger_id,
            'driver_id' => $driver->driver_id,
            'vehicle_id' => $vehicle->vehicle_id,
        ]);

        Payment::factory(2)->create([
            'booking_id' => $booking->booking_id,
        ]);

        $this->assertCount(2, $booking->payments);
        $this->assertInstanceOf(Payment::class, $booking->payments->first());
    }

    public function test_payment_belongs_to_booking(): void
    {
        $passenger = Passenger::factory()->create();
        $driver = Driver::factory()->create(['status' => 'Available']);
        $vehicle = Vehicle::factory()->create(['status' => 'Available']);

        $booking = Booking::factory()->create([
            'passenger_id' => $passenger->passenger_id,
            'driver_id' => $driver->driver_id,
            'vehicle_id' => $vehicle->vehicle_id,
        ]);

        $payment = Payment::factory()->create([
            'booking_id' => $booking->booking_id,
        ]);

        $this->assertInstanceOf(Booking::class, $payment->booking);
        $this->assertEquals($booking->booking_id, $payment->booking->booking_id);
    }

    public function test_booking_cascade_delete_removes_payments(): void
    {
        $passenger = Passenger::factory()->create();
        $driver = Driver::factory()->create(['status' => 'Available']);
        $vehicle = Vehicle::factory()->create(['status' => 'Available']);

        $booking = Booking::factory()->create([
            'passenger_id' => $passenger->passenger_id,
            'driver_id' => $driver->driver_id,
            'vehicle_id' => $vehicle->vehicle_id,
        ]);

        $payment = Payment::factory()->create([
            'booking_id' => $booking->booking_id,
        ]);

        $this->assertDatabaseHas('payments', ['payment_id' => $payment->payment_id]);

        $booking->delete();

        $this->assertDatabaseMissing('payments', ['payment_id' => $payment->payment_id]);
    }
}
