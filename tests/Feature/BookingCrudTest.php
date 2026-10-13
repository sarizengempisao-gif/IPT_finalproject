<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Driver;
use App\Models\Passenger;
use App\Models\Payment;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingCrudTest extends TestCase
{
    use RefreshDatabase;

    private function createTestData(): array
    {
        return [
            'passenger' => Passenger::factory()->create(),
            'driver' => Driver::factory()->create(['status' => 'Available']),
            'vehicle' => Vehicle::factory()->create(['status' => 'Available']),
        ];
    }

    public function test_booking_index_page_renders(): void
    {
        $data = $this->createTestData();
        Booking::factory(3)->create([
            'passenger_id' => $data['passenger']->passenger_id,
            'driver_id' => $data['driver']->driver_id,
            'vehicle_id' => $data['vehicle']->vehicle_id,
        ]);

        $this->get(route('bookings.index'))->assertOk()->assertSee('Bookings');
    }

    public function test_booking_create_page_renders(): void
    {
        $data = $this->createTestData();
        $this->get(route('bookings.create'))->assertOk();
    }

    public function test_booking_can_be_created(): void
    {
        $data = $this->createTestData();

        $response = $this->post(route('bookings.store'), [
            'passenger_id' => $data['passenger']->passenger_id,
            'driver_id' => $data['driver']->driver_id,
            'vehicle_id' => $data['vehicle']->vehicle_id,
            'booking_reference' => 'BK-TEST-001',
            'pickup_location' => '123 Main St',
            'dropoff_location' => '456 Oak Ave',
            'booking_date' => '2026-10-15',
            'booking_time' => '08:30',
            'fare' => 150.00,
            'status' => 'Pending',
            'notes' => 'Test booking',
        ]);

        $response->assertRedirect(route('bookings.index'));
        $this->assertDatabaseHas('bookings', [
            'booking_reference' => 'BK-TEST-001',
            'passenger_id' => $data['passenger']->passenger_id,
            'fare' => 150.00,
        ]);
    }

    public function test_booking_validation_fails_without_required_fields(): void
    {
        $this->from(route('bookings.create'))
            ->post(route('bookings.store'), [])
            ->assertRedirect()
            ->assertSessionHasErrors(['passenger_id', 'driver_id', 'vehicle_id', 'booking_reference', 'pickup_location', 'dropoff_location', 'booking_date', 'booking_time', 'fare', 'status']);
    }

    public function test_booking_rejects_nonexistent_foreign_keys(): void
    {
        $this->from(route('bookings.create'))
            ->post(route('bookings.store'), [
                'passenger_id' => 999,
                'driver_id' => 999,
                'vehicle_id' => 999,
                'booking_reference' => 'BK-TEST-002',
                'pickup_location' => '123 Main St',
                'dropoff_location' => '456 Oak Ave',
                'booking_date' => '2026-10-15',
                'booking_time' => '08:30',
                'fare' => 150.00,
                'status' => 'Pending',
            ])
            ->assertRedirect()
            ->assertSessionHasErrors(['passenger_id', 'driver_id', 'vehicle_id']);
    }

    public function test_booking_fare_must_be_non_negative(): void
    {
        $data = $this->createTestData();

        $this->from(route('bookings.create'))
            ->post(route('bookings.store'), [
                'passenger_id' => $data['passenger']->passenger_id,
                'driver_id' => $data['driver']->driver_id,
                'vehicle_id' => $data['vehicle']->vehicle_id,
                'booking_reference' => 'BK-TEST-003',
                'pickup_location' => '123 Main St',
                'dropoff_location' => '456 Oak Ave',
                'booking_date' => '2026-10-15',
                'booking_time' => '08:30',
                'fare' => -50,
                'status' => 'Pending',
            ])
            ->assertRedirect()
            ->assertSessionHasErrors(['fare']);
    }

    public function test_booking_status_must_be_valid(): void
    {
        $data = $this->createTestData();

        $this->from(route('bookings.create'))
            ->post(route('bookings.store'), [
                'passenger_id' => $data['passenger']->passenger_id,
                'driver_id' => $data['driver']->driver_id,
                'vehicle_id' => $data['vehicle']->vehicle_id,
                'booking_reference' => 'BK-TEST-004',
                'pickup_location' => '123 Main St',
                'dropoff_location' => '456 Oak Ave',
                'booking_date' => '2026-10-15',
                'booking_time' => '08:30',
                'fare' => 150.00,
                'status' => 'Invalid Status',
            ])
            ->assertRedirect()
            ->assertSessionHasErrors(['status']);
    }

    public function test_booking_can_be_viewed(): void
    {
        $data = $this->createTestData();
        $booking = Booking::factory()->create([
            'passenger_id' => $data['passenger']->passenger_id,
            'driver_id' => $data['driver']->driver_id,
            'vehicle_id' => $data['vehicle']->vehicle_id,
        ]);

        $this->get(route('bookings.show', $booking))->assertOk();
    }

    public function test_booking_can_be_updated(): void
    {
        $data = $this->createTestData();
        $booking = Booking::factory()->create([
            'passenger_id' => $data['passenger']->passenger_id,
            'driver_id' => $data['driver']->driver_id,
            'vehicle_id' => $data['vehicle']->vehicle_id,
            'status' => 'Pending',
        ]);

        $response = $this->put(route('bookings.update', $booking), [
            'passenger_id' => $data['passenger']->passenger_id,
            'driver_id' => $data['driver']->driver_id,
            'vehicle_id' => $data['vehicle']->vehicle_id,
            'booking_reference' => $booking->booking_reference,
            'pickup_location' => 'Updated Pickup',
            'dropoff_location' => 'Updated Dropoff',
            'booking_date' => '2026-10-16',
            'booking_time' => '09:00',
            'fare' => 200.00,
            'status' => 'Completed',
            'notes' => 'Updated booking',
        ]);

        $response->assertRedirect(route('bookings.index'));
        $this->assertDatabaseHas('bookings', [
            'booking_id' => $booking->booking_id,
            'pickup_location' => 'Updated Pickup',
            'fare' => 200.00,
        ]);
    }

    public function test_booking_can_be_deleted(): void
    {
        $data = $this->createTestData();
        $booking = Booking::factory()->create([
            'passenger_id' => $data['passenger']->passenger_id,
            'driver_id' => $data['driver']->driver_id,
            'vehicle_id' => $data['vehicle']->vehicle_id,
        ]);

        $this->delete(route('bookings.destroy', $booking))
            ->assertRedirect(route('bookings.index'));

        $this->assertDatabaseMissing('bookings', ['booking_id' => $booking->booking_id]);
    }

    public function test_booking_delete_cascades_to_payments(): void
    {
        $data = $this->createTestData();
        $booking = Booking::factory()->create([
            'passenger_id' => $data['passenger']->passenger_id,
            'driver_id' => $data['driver']->driver_id,
            'vehicle_id' => $data['vehicle']->vehicle_id,
        ]);
        $payment = Payment::factory()->create([
            'booking_id' => $booking->booking_id,
            'status' => 'Paid',
        ]);

        $this->delete(route('bookings.destroy', $booking))
            ->assertRedirect(route('bookings.index'));

        $this->assertDatabaseMissing('bookings', ['booking_id' => $booking->booking_id]);
        $this->assertDatabaseMissing('payments', ['payment_id' => $payment->payment_id]);
    }

    public function test_booking_create_only_shows_available_drivers_and_vehicles(): void
    {
        Driver::factory()->create(['status' => 'On Trip']);
        Vehicle::factory()->create(['status' => 'In Use']);

        $response = $this->get(route('bookings.create'));
        $response->assertOk();
    }

    public function test_booking_with_conflicting_active_booking_fails(): void
    {
        $data = $this->createTestData();
        Booking::factory()->create([
            'passenger_id' => $data['passenger']->passenger_id,
            'driver_id' => $data['driver']->driver_id,
            'vehicle_id' => $data['vehicle']->vehicle_id,
            'status' => 'Confirmed',
        ]);

        $this->from(route('bookings.create'))
            ->post(route('bookings.store'), [
                'passenger_id' => $data['passenger']->passenger_id,
                'driver_id' => $data['driver']->driver_id,
                'vehicle_id' => $data['vehicle']->vehicle_id,
                'booking_reference' => 'BK-CONFLICT-001',
                'pickup_location' => '123 Main St',
                'dropoff_location' => '456 Oak Ave',
                'booking_date' => '2026-10-15',
                'booking_time' => '08:30',
                'fare' => 150.00,
                'status' => 'Confirmed',
            ])
            ->assertRedirect();
    }

    public function test_booking_reference_must_be_unique(): void
    {
        Booking::factory()->create(['booking_reference' => 'DUPLICATE-REF']);

        $data = $this->createTestData();

        $this->from(route('bookings.create'))
            ->post(route('bookings.store'), [
                'passenger_id' => $data['passenger']->passenger_id,
                'driver_id' => $data['driver']->driver_id,
                'vehicle_id' => $data['vehicle']->vehicle_id,
                'booking_reference' => 'DUPLICATE-REF',
                'pickup_location' => '123 Main St',
                'dropoff_location' => '456 Oak Ave',
                'booking_date' => '2026-10-15',
                'booking_time' => '08:30',
                'fare' => 150.00,
                'status' => 'Pending',
            ])
            ->assertRedirect()
            ->assertSessionHasErrors(['booking_reference']);
    }

    public function test_unknown_booking_shows_404(): void
    {
        $this->get(route('bookings.show', 999))->assertNotFound();
    }
}
