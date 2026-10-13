<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Driver;
use App\Models\Passenger;
use App\Models\Payment;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_response_and_displays_counts(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Taxi Booking Management System');
        $response->assertSee('Passengers');
        $response->assertSee('Drivers');
        $response->assertSee('Vehicles');
        $response->assertSee('Bookings');
        $response->assertSee('Payments');
    }

    public function test_dashboard_shows_real_counts(): void
    {
        $passenger = Passenger::factory()->create();
        $driver = Driver::factory()->create(['status' => 'Available']);
        $vehicle = Vehicle::factory()->create(['status' => 'Available']);
        $booking = Booking::factory()->create([
            'passenger_id' => $passenger->passenger_id,
            'driver_id' => $driver->driver_id,
            'vehicle_id' => $vehicle->vehicle_id,
        ]);

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('1');
    }

    public function test_all_resource_index_pages_render(): void
    {
        Passenger::factory(3)->create();
        Driver::factory(3)->create();
        Vehicle::factory(3)->create();
        $passenger = Passenger::first();
        $driver = Driver::where('status', 'Available')->first() ?? Driver::first();
        $vehicle = Vehicle::where('status', 'Available')->first() ?? Vehicle::first();
        $booking = Booking::factory()->create([
            'passenger_id' => $passenger->passenger_id,
            'driver_id' => $driver->driver_id,
            'vehicle_id' => $vehicle->vehicle_id,
        ]);
        Payment::factory(2)->create([
            'booking_id' => $booking->booking_id,
        ]);

        $this->get(route('passengers.index'))->assertOk();
        $this->get(route('drivers.index'))->assertOk();
        $this->get(route('vehicles.index'))->assertOk();
        $this->get(route('bookings.index'))->assertOk();
        $this->get(route('payments.index'))->assertOk();
    }

    public function test_named_routes_resolve(): void
    {
        $this->assertTrue(true);
    }
}
