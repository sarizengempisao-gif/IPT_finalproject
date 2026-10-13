<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Driver;
use App\Models\Passenger;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VehicleCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_vehicle_index_page_renders(): void
    {
        Vehicle::factory(3)->create();
        $this->get(route('vehicles.index'))->assertOk()->assertSee('Vehicles');
    }

    public function test_vehicle_can_be_created(): void
    {
        $response = $this->post(route('vehicles.store'), [
            'plate_number' => 'XYZ-9999',
            'vehicle_type' => 'SUV',
            'model' => 'Toyota Fortuner',
            'year' => 2023,
            'color' => 'White',
            'capacity' => 7,
            'status' => 'Available',
        ]);

        $response->assertRedirect(route('vehicles.index'));
        $this->assertDatabaseHas('vehicles', [
            'plate_number' => 'XYZ-9999',
            'model' => 'Toyota Fortuner',
            'year' => 2023,
        ]);
    }

    public function test_vehicle_validation_fails_without_required_fields(): void
    {
        $this->from(route('vehicles.create'))
            ->post(route('vehicles.store'), [])
            ->assertRedirect()
            ->assertSessionHasErrors(['plate_number', 'vehicle_type', 'model', 'year', 'color', 'capacity', 'status']);
    }

    public function test_vehicle_plate_number_must_be_unique(): void
    {
        Vehicle::factory()->create(['plate_number' => 'ABC-1234']);

        $this->from(route('vehicles.create'))
            ->post(route('vehicles.store'), [
                'plate_number' => 'ABC-1234',
                'vehicle_type' => 'SUV',
                'model' => 'Toyota Fortuner',
                'year' => 2023,
                'color' => 'White',
                'capacity' => 7,
                'status' => 'Available',
            ])
            ->assertRedirect()
            ->assertSessionHasErrors(['plate_number']);
    }

    public function test_vehicle_capacity_must_be_in_valid_range(): void
    {
        $this->from(route('vehicles.create'))
            ->post(route('vehicles.store'), [
                'plate_number' => 'XYZ-9999',
                'vehicle_type' => 'SUV',
                'model' => 'Toyota Fortuner',
                'year' => 2023,
                'color' => 'White',
                'capacity' => 50,
                'status' => 'Available',
            ])
            ->assertRedirect()
            ->assertSessionHasErrors(['capacity']);
    }

    public function test_vehicle_year_must_be_valid(): void
    {
        $this->from(route('vehicles.create'))
            ->post(route('vehicles.store'), [
                'plate_number' => 'XYZ-9999',
                'vehicle_type' => 'SUV',
                'model' => 'Toyota Fortuner',
                'year' => 1800,
                'color' => 'White',
                'capacity' => 7,
                'status' => 'Available',
            ])
            ->assertRedirect()
            ->assertSessionHasErrors(['year']);
    }

    public function test_vehicle_status_only_accepts_valid_values(): void
    {
        $this->from(route('vehicles.create'))
            ->post(route('vehicles.store'), [
                'plate_number' => 'XYZ-9999',
                'vehicle_type' => 'SUV',
                'model' => 'Toyota Fortuner',
                'year' => 2023,
                'color' => 'White',
                'capacity' => 7,
                'status' => 'Invalid Status',
            ])
            ->assertRedirect()
            ->assertSessionHasErrors(['status']);
    }

    public function test_vehicle_can_be_viewed(): void
    {
        $vehicle = Vehicle::factory()->create();
        $this->get(route('vehicles.show', $vehicle))->assertOk();
    }

    public function test_vehicle_can_be_updated(): void
    {
        $vehicle = Vehicle::factory()->create(['model' => 'Old Model']);

        $response = $this->put(route('vehicles.update', $vehicle), [
            'plate_number' => $vehicle->plate_number,
            'vehicle_type' => 'SUV',
            'model' => 'Updated Model',
            'year' => 2024,
            'color' => 'Black',
            'capacity' => 6,
            'status' => 'Available',
        ]);

        $response->assertRedirect(route('vehicles.index'));
        $this->assertDatabaseHas('vehicles', ['model' => 'Updated Model']);
    }

    public function test_vehicle_cannot_be_deleted_with_bookings(): void
    {
        $vehicle = Vehicle::factory()->create(['status' => 'Available']);
        $passenger = Passenger::factory()->create();
        $driver = Driver::factory()->create(['status' => 'Available']);
        Booking::factory()->create([
            'passenger_id' => $passenger->passenger_id,
            'driver_id' => $driver->driver_id,
            'vehicle_id' => $vehicle->vehicle_id,
        ]);

        $response = $this->delete(route('vehicles.destroy', $vehicle));

        $response->assertRedirect();
        $this->assertTrue($vehicle->fresh()->exists);
    }

    public function test_vehicle_without_bookings_can_be_deleted(): void
    {
        $vehicle = Vehicle::factory()->create();

        $this->delete(route('vehicles.destroy', $vehicle))
            ->assertRedirect(route('vehicles.index'));

        $this->assertDatabaseMissing('vehicles', ['vehicle_id' => $vehicle->vehicle_id]);
    }
}
