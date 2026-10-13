<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Driver;
use App\Models\Passenger;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class BookingController extends Controller
{
    public function index()
    {
        return view('bookings.index', ['bookings' => Booking::with(['passenger', 'driver', 'vehicle', 'payments'])->latest('booking_date')->latest('booking_time')->get()]);
    }

    public function create()
    {
        return view('bookings.create', ['passengers' => Passenger::orderBy('full_name')->get(), 'drivers' => Driver::where('status', 'Available')->orderBy('full_name')->get(), 'vehicles' => Vehicle::where('status', 'Available')->orderBy('plate_number')->get()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules());
        $this->validateAvailability($data['passenger_id'], $data['driver_id'], $data['vehicle_id'], $data['status'], $request);
        DB::transaction(function () use ($data) {
            Booking::create($data);
            $this->syncAvailability($data);
        });

        return to_route('bookings.index')->with('success', 'Booking created successfully.');
    }

    public function show(Booking $booking)
    {
        $booking->load(['passenger', 'driver', 'vehicle', 'payments']);

        return view('bookings.show', compact('booking'));
    }

    public function edit(Booking $booking)
    {
        return view('bookings.edit', ['booking' => $booking, 'passengers' => Passenger::orderBy('full_name')->get(), 'drivers' => Driver::orderBy('full_name')->get(), 'vehicles' => Vehicle::orderBy('plate_number')->get()]);
    }

    public function update(Request $request, Booking $booking)
    {
        $data = $request->validate($this->rules($booking));
        $this->validateAvailability($data['passenger_id'], $data['driver_id'], $data['vehicle_id'], $data['status'], $request, $booking);
        DB::transaction(function () use ($booking, $data) {
            $booking->update($data);
            $this->syncAvailability($data, $booking);
        });

        return to_route('bookings.index')->with('success', 'Booking updated successfully.');
    }

    public function destroy(Booking $booking)
    {
        $booking->delete();

        return to_route('bookings.index')->with('success', 'Booking deleted successfully.');
    }

    private function rules(?Booking $booking = null): array
    {
        return ['passenger_id' => 'required|exists:passengers,passenger_id', 'driver_id' => 'required|exists:drivers,driver_id', 'vehicle_id' => 'required|exists:vehicles,vehicle_id', 'booking_reference' => ['required', 'string', 'max:50', $booking ? Rule::unique('bookings', 'booking_reference')->ignore($booking->booking_id, 'booking_id') : 'unique:bookings,booking_reference'], 'pickup_location' => 'required|string|max:255', 'dropoff_location' => 'required|string|max:255', 'booking_date' => 'required|date', 'booking_time' => 'required', 'fare' => 'required|numeric|min:0', 'status' => 'required|in:Pending,Confirmed,Completed,Cancelled', 'notes' => 'nullable|string|max:1000'];
    }

    private function validateAvailability($passengerId, $driverId, $vehicleId, $status, Request $request, ?Booking $currentBooking = null): void
    {
        if (in_array($status, ['Confirmed', 'In Progress'])) {
            $driver = Driver::findOrFail($driverId);
            if ($driver->status !== 'Available') {
                $request->validate(['driver_id' => 'required|in:'.$driverId], [], ['driver_id.required' => 'The selected driver is not available for new bookings.']);
            }
            $vehicle = Vehicle::findOrFail($vehicleId);
            if ($vehicle->status !== 'Available') {
                $request->validate(['vehicle_id' => 'required|in:'.$vehicleId], [], ['vehicle_id.required' => 'The selected vehicle is not available for new bookings.']);
            }
            $conflict = Booking::where(function ($q) use ($driverId, $vehicleId) {
                $q->where('driver_id', $driverId)->orWhere('vehicle_id', $vehicleId);
            })->whereIn('status', ['Pending', 'Confirmed', 'In Progress'])->when($currentBooking, function ($q) use ($currentBooking) {
                $q->where('booking_id', '!=', $currentBooking->booking_id);
            })->exists();
            if ($conflict) {
                $request->validate(['driver_id' => 'required|in:'.$driverId], [], ['driver_id.required' => 'This driver or vehicle already has an active booking.']);
            }
        }
    }

    private function syncAvailability(array $data, ?Booking $booking = null): void
    {
        $status = $data['status'];
        $driver = Driver::findOrFail($data['driver_id']);
        $vehicle = Vehicle::findOrFail($data['vehicle_id']);
        if (in_array($status, ['Confirmed', 'In Progress'])) {
            $driver->status = 'On Trip';
            $vehicle->status = 'In Use';
            $driver->save();
            $vehicle->save();
        }
        if ($status === 'Completed' || $status === 'Cancelled') {
            $driver->status = 'Available';
            $vehicle->status = 'Available';
            $driver->save();
            $vehicle->save();
        }
    }
}
