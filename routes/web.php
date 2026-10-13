<?php

use App\Http\Controllers\BookingController;
use App\Http\Controllers\DriverController;
use App\Http\Controllers\PassengerController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\VehicleController;
use App\Models\Booking;
use App\Models\Driver;
use App\Models\Passenger;
use App\Models\Payment;
use App\Models\Vehicle;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('dashboard', [
        'counts' => [
            'passengers' => Passenger::count(), 'drivers' => Driver::count(), 'vehicles' => Vehicle::count(),
            'bookings' => Booking::count(), 'payments' => Payment::count(),
        ],
        'recentBookings' => Booking::with(['passenger', 'driver', 'vehicle', 'payments'])->latest('booking_date')->latest('booking_time')->take(6)->get(),
        'pendingPayments' => Payment::with('booking.passenger')->where('status', 'Pending')->latest('payment_date')->take(5)->get(),
    ]);
})->name('dashboard');
Route::resource('passengers', PassengerController::class);
Route::resource('drivers', DriverController::class);
Route::resource('vehicles', VehicleController::class);
Route::resource('bookings', BookingController::class);
Route::resource('payments', PaymentController::class);
