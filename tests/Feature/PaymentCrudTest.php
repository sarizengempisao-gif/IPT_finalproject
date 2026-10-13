<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Driver;
use App\Models\Passenger;
use App\Models\Payment;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentCrudTest extends TestCase
{
    use RefreshDatabase;

    private function createTestData(): array
    {
        $passenger = Passenger::factory()->create();
        $driver = Driver::factory()->create(['status' => 'Available']);
        $vehicle = Vehicle::factory()->create(['status' => 'Available']);
        $booking = Booking::factory()->create([
            'passenger_id' => $passenger->passenger_id,
            'driver_id' => $driver->driver_id,
            'vehicle_id' => $vehicle->vehicle_id,
        ]);

        return ['passenger' => $passenger, 'driver' => $driver, 'vehicle' => $vehicle, 'booking' => $booking];
    }

    public function test_payment_index_page_renders(): void
    {
        $data = $this->createTestData();
        Payment::factory(2)->create([
            'booking_id' => $data['booking']->booking_id,
        ]);

        $this->get(route('payments.index'))->assertOk()->assertSee('Payments');
    }

    public function test_payment_create_page_renders(): void
    {
        $data = $this->createTestData();
        $this->get(route('payments.create'))->assertOk();
    }

    public function test_payment_can_be_created(): void
    {
        $data = $this->createTestData();

        $response = $this->post(route('payments.store'), [
            'booking_id' => $data['booking']->booking_id,
            'payment_reference' => 'PAY-000001',
            'amount' => 150.00,
            'payment_method' => 'Cash',
            'payment_date' => '2026-10-15',
            'status' => 'Paid',
            'notes' => 'Cash payment',
        ]);

        $response->assertRedirect(route('payments.index'));
        $this->assertDatabaseHas('payments', [
            'payment_reference' => 'PAY-000001',
            'booking_id' => $data['booking']->booking_id,
            'amount' => 150.00,
        ]);
    }

    public function test_payment_validation_fails_without_required_fields(): void
    {
        $this->from(route('payments.create'))
            ->post(route('payments.store'), [])
            ->assertRedirect()
            ->assertSessionHasErrors(['booking_id', 'payment_reference', 'amount', 'payment_method', 'payment_date', 'status']);
    }

    public function test_payment_rejects_nonexistent_booking(): void
    {
        $this->from(route('payments.create'))
            ->post(route('payments.store'), [
                'booking_id' => 999,
                'payment_reference' => 'PAY-000002',
                'amount' => 150.00,
                'payment_method' => 'Cash',
                'payment_date' => '2026-10-15',
                'status' => 'Paid',
            ])
            ->assertRedirect()
            ->assertSessionHasErrors(['booking_id']);
    }

    public function test_payment_amount_must_be_non_negative(): void
    {
        $data = $this->createTestData();

        $this->from(route('payments.create'))
            ->post(route('payments.store'), [
                'booking_id' => $data['booking']->booking_id,
                'payment_reference' => 'PAY-000003',
                'amount' => -50,
                'payment_method' => 'Cash',
                'payment_date' => '2026-10-15',
                'status' => 'Paid',
            ])
            ->assertRedirect()
            ->assertSessionHasErrors(['amount']);
    }

    public function test_payment_method_must_be_valid(): void
    {
        $data = $this->createTestData();

        $this->from(route('payments.create'))
            ->post(route('payments.store'), [
                'booking_id' => $data['booking']->booking_id,
                'payment_reference' => 'PAY-000004',
                'amount' => 150.00,
                'payment_method' => 'Invalid Method',
                'payment_date' => '2026-10-15',
                'status' => 'Paid',
            ])
            ->assertRedirect()
            ->assertSessionHasErrors(['payment_method']);
    }

    public function test_payment_status_must_be_valid(): void
    {
        $data = $this->createTestData();

        $this->from(route('payments.create'))
            ->post(route('payments.store'), [
                'booking_id' => $data['booking']->booking_id,
                'payment_reference' => 'PAY-000005',
                'amount' => 150.00,
                'payment_method' => 'Cash',
                'payment_date' => '2026-10-15',
                'status' => 'Invalid Status',
            ])
            ->assertRedirect()
            ->assertSessionHasErrors(['status']);
    }

    public function test_payment_reference_must_be_unique(): void
    {
        $data = $this->createTestData();
        Payment::factory()->create(['payment_reference' => 'DUPLICATE-PAY']);

        $this->from(route('payments.create'))
            ->post(route('payments.store'), [
                'booking_id' => $data['booking']->booking_id,
                'payment_reference' => 'DUPLICATE-PAY',
                'amount' => 150.00,
                'payment_method' => 'Cash',
                'payment_date' => '2026-10-15',
                'status' => 'Paid',
            ])
            ->assertRedirect()
            ->assertSessionHasErrors(['payment_reference']);
    }

    public function test_payment_can_be_viewed(): void
    {
        $data = $this->createTestData();
        $payment = Payment::factory()->create([
            'booking_id' => $data['booking']->booking_id,
            'status' => 'Paid',
        ]);

        $this->get(route('payments.show', $payment))->assertOk();
    }

    public function test_payment_can_be_updated(): void
    {
        $data = $this->createTestData();
        $payment = Payment::factory()->create([
            'booking_id' => $data['booking']->booking_id,
            'payment_reference' => 'PAY-000001',
            'amount' => 150.00,
            'status' => 'Pending',
        ]);

        $response = $this->put(route('payments.update', $payment), [
            'booking_id' => $data['booking']->booking_id,
            'payment_reference' => 'PAY-000001',
            'amount' => 200.00,
            'payment_method' => 'GCash',
            'payment_date' => '2026-10-16',
            'status' => 'Paid',
        ]);

        $response->assertRedirect(route('payments.index'));
        $this->assertDatabaseHas('payments', [
            'payment_id' => $payment->payment_id,
            'amount' => 200.00,
            'status' => 'Paid',
        ]);
    }

    public function test_payment_can_be_deleted(): void
    {
        $data = $this->createTestData();
        $payment = Payment::factory()->create([
            'booking_id' => $data['booking']->booking_id,
        ]);

        $this->delete(route('payments.destroy', $payment))
            ->assertRedirect(route('payments.index'));

        $this->assertDatabaseMissing('payments', ['payment_id' => $payment->payment_id]);
    }

    public function test_unknown_payment_shows_404(): void
    {
        $this->get(route('payments.show', 999))->assertNotFound();
    }
}
