<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Driver;
use App\Models\Passenger;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DriverCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_driver_index_page_renders(): void
    {
        Driver::factory(3)->create();
        $this->get(route('drivers.index'))->assertOk()->assertSee('Drivers');
    }

    public function test_driver_can_be_created(): void
    {
        $response = $this->post(route('drivers.store'), [
            'full_name' => 'Jane Driver',
            'email' => 'jane@example.com',
            'license_no' => 'DL-123456',
            'phone' => '09123456789',
            'status' => 'Available',
        ]);

        $response->assertRedirect(route('drivers.index'));
        $this->assertDatabaseHas('drivers', [
            'full_name' => 'Jane Driver',
            'email' => 'jane@example.com',
            'license_no' => 'DL-123456',
        ]);
    }

    public function test_driver_validation_fails_without_required_fields(): void
    {
        $this->from(route('drivers.create'))
            ->post(route('drivers.store'), [])
            ->assertRedirect()
            ->assertSessionHasErrors(['full_name', 'license_no', 'phone', 'status']);
    }

    public function test_driver_license_no_must_be_unique(): void
    {
        Driver::factory()->create(['license_no' => 'DL-999999']);

        $this->from(route('drivers.create'))
            ->post(route('drivers.store'), [
                'full_name' => 'Jane Driver',
                'license_no' => 'DL-999999',
                'phone' => '09123456789',
                'status' => 'Available',
            ])
            ->assertRedirect()
            ->assertSessionHasErrors(['license_no']);
    }

    public function test_driver_email_must_be_unique(): void
    {
        Driver::factory()->create(['email' => 'test@example.com']);

        $this->from(route('drivers.create'))
            ->post(route('drivers.store'), [
                'full_name' => 'Jane Driver',
                'email' => 'test@example.com',
                'license_no' => 'DL-123456',
                'phone' => '09123456789',
                'status' => 'Available',
            ])
            ->assertRedirect()
            ->assertSessionHasErrors(['email']);
    }

    public function test_driver_status_only_accepts_valid_values(): void
    {
        $this->from(route('drivers.create'))
            ->post(route('drivers.store'), [
                'full_name' => 'Jane Driver',
                'license_no' => 'DL-123456',
                'phone' => '09123456789',
                'status' => 'Invalid Status',
            ])
            ->assertRedirect()
            ->assertSessionHasErrors(['status']);
    }

    public function test_driver_can_be_viewed(): void
    {
        $driver = Driver::factory()->create(['status' => 'Available']);
        $this->get(route('drivers.show', $driver))->assertOk();
    }

    public function test_driver_can_be_updated(): void
    {
        $driver = Driver::factory()->create(['full_name' => 'Old Name']);

        $response = $this->put(route('drivers.update', $driver), [
            'full_name' => 'Updated Name',
            'email' => 'updated@example.com',
            'license_no' => $driver->license_no,
            'phone' => '09876543210',
            'status' => 'Available',
        ]);

        $response->assertRedirect(route('drivers.index'));
        $this->assertDatabaseHas('drivers', ['full_name' => 'Updated Name']);
    }

    public function test_driver_cannot_be_deleted_with_bookings(): void
    {
        $driver = Driver::factory()->create(['status' => 'Available']);
        $passenger = Passenger::factory()->create();
        $vehicle = Vehicle::factory()->create(['status' => 'Available']);
        Booking::factory()->create([
            'passenger_id' => $passenger->passenger_id,
            'driver_id' => $driver->driver_id,
            'vehicle_id' => $vehicle->vehicle_id,
        ]);

        $response = $this->delete(route('drivers.destroy', $driver));

        $response->assertRedirect();
        $this->assertTrue($driver->fresh()->exists);
    }

    public function test_driver_without_bookings_can_be_deleted(): void
    {
        $driver = Driver::factory()->create();

        $this->delete(route('drivers.destroy', $driver))
            ->assertRedirect(route('drivers.index'));

        $this->assertDatabaseMissing('drivers', ['driver_id' => $driver->driver_id]);
    }
}
