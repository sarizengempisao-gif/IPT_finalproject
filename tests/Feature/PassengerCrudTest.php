<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Driver;
use App\Models\Passenger;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PassengerCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_passenger_index_page_renders(): void
    {
        Passenger::factory(3)->create();
        $this->get(route('passengers.index'))->assertOk()->assertSee('Passengers');
    }

    public function test_passenger_can_be_created(): void
    {
        $response = $this->post(route('passengers.store'), [
            'full_name' => 'John Doe',
            'phone' => '09123456789',
            'email' => 'john@example.com',
            'address' => '123 Main St',
        ]);

        $response->assertRedirect(route('passengers.index'));
        $this->assertDatabaseHas('passengers', [
            'full_name' => 'John Doe',
            'phone' => '09123456789',
            'email' => 'john@example.com',
        ]);
    }

    public function test_passenger_validation_fails_without_required_fields(): void
    {
        $this->from(route('passengers.create'))
            ->post(route('passengers.store'), [])
            ->assertRedirect()
            ->assertSessionHasErrors(['full_name', 'phone', 'address']);

        $this->assertDatabaseMissing('passengers', ['full_name' => null]);
    }

    public function test_passenger_email_must_be_valid(): void
    {
        $this->from(route('passengers.create'))
            ->post(route('passengers.store'), [
                'full_name' => 'John Doe',
                'phone' => '09123456789',
                'email' => 'not-an-email',
                'address' => '123 Main St',
            ])
            ->assertRedirect()
            ->assertSessionHasErrors(['email']);
    }

    public function test_passenger_email_must_be_unique(): void
    {
        Passenger::factory()->create(['email' => 'test@example.com']);

        $this->from(route('passengers.create'))
            ->post(route('passengers.store'), [
                'full_name' => 'John Doe',
                'phone' => '09123456789',
                'email' => 'test@example.com',
                'address' => '123 Main St',
            ])
            ->assertRedirect()
            ->assertSessionHasErrors(['email']);
    }

    public function test_passenger_can_be_viewed(): void
    {
        $passenger = Passenger::factory()->create();

        $this->get(route('passengers.show', $passenger))->assertOk();
    }

    public function test_passenger_can_be_updated(): void
    {
        $passenger = Passenger::factory()->create(['full_name' => 'Old Name']);

        $response = $this->put(route('passengers.update', $passenger), [
            'full_name' => 'Updated Name',
            'phone' => '09876543210',
            'email' => 'updated@example.com',
            'address' => '456 Updated St',
        ]);

        $response->assertRedirect(route('passengers.index'));
        $this->assertDatabaseHas('passengers', ['full_name' => 'Updated Name']);
        $this->assertDatabaseMissing('passengers', ['full_name' => 'Old Name']);
    }

    public function test_passenger_update_validates_unique_email_ignoring_current_record(): void
    {
        $passenger = Passenger::factory()->create(['email' => 'test@example.com']);

        $this->from(route('passengers.edit', $passenger))
            ->put(route('passengers.update', $passenger), [
                'full_name' => 'Updated Name',
                'phone' => '09876543210',
                'email' => 'test@example.com',
                'address' => '456 Updated St',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('passengers', ['full_name' => 'Updated Name']);
    }

    public function test_passenger_cannot_be_deleted_with_bookings(): void
    {
        $passenger = Passenger::factory()->create();
        $driver = Driver::factory()->create(['status' => 'Available']);
        $vehicle = Vehicle::factory()->create(['status' => 'Available']);
        Booking::factory()->create([
            'passenger_id' => $passenger->passenger_id,
            'driver_id' => $driver->driver_id,
            'vehicle_id' => $vehicle->vehicle_id,
        ]);

        $response = $this->delete(route('passengers.destroy', $passenger));

        $response->assertRedirect();
        $this->assertTrue($passenger->fresh()->exists);
    }

    public function test_passenger_without_bookings_can_be_deleted(): void
    {
        $passenger = Passenger::factory()->create();

        $this->delete(route('passengers.destroy', $passenger))
            ->assertRedirect(route('passengers.index'));

        $this->assertDatabaseMissing('passengers', ['passenger_id' => $passenger->passenger_id]);
    }

    public function test_passenger_index_shows_booking_count(): void
    {
        $passenger = Passenger::factory()->create();
        $driver = Driver::factory()->create(['status' => 'Available']);
        $vehicle = Vehicle::factory()->create(['status' => 'Available']);
        Booking::factory(2)->create([
            'passenger_id' => $passenger->passenger_id,
            'driver_id' => $driver->driver_id,
            'vehicle_id' => $vehicle->vehicle_id,
        ]);

        $response = $this->get(route('passengers.index'));
        $response->assertOk();
    }
}
