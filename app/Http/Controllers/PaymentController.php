<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PaymentController extends Controller
{
    public function index()
    {
        return view('payments.index', ['payments' => Payment::with('booking.passenger')->latest('payment_date')->latest('payment_id')->get()]);
    }

    public function create()
    {
        return view('payments.create', ['bookings' => Booking::with('passenger')->orderByDesc('booking_date')->get()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules());
        Payment::create($data);

        return to_route('payments.index')->with('success', 'Payment created successfully.');
    }

    public function show(Payment $payment)
    {
        $payment->load('booking.passenger', 'booking.driver', 'booking.vehicle');

        return view('payments.show', compact('payment'));
    }

    public function edit(Payment $payment)
    {
        return view('payments.edit', ['payment' => $payment, 'bookings' => Booking::with('passenger')->orderByDesc('booking_date')->get()]);
    }

    public function update(Request $request, Payment $payment)
    {
        $data = $request->validate($this->rules($payment));
        $payment->update($data);

        return to_route('payments.index')->with('success', 'Payment updated successfully.');
    }

    public function destroy(Payment $payment)
    {
        $payment->delete();

        return to_route('payments.index')->with('success', 'Payment deleted successfully.');
    }

    private function rules(?Payment $payment = null): array
    {
        return ['booking_id' => 'required|exists:bookings,booking_id', 'payment_reference' => ['required', 'string', 'max:50', Rule::unique('payments', 'payment_reference')->ignore($payment?->payment_id, 'payment_id')], 'amount' => 'required|numeric|min:0', 'payment_method' => 'required|in:Cash,GCash,Card,Bank Transfer', 'payment_date' => 'required|date', 'status' => 'required|in:Paid,Pending,Refunded', 'notes' => 'nullable|string|max:1000'];
    }
}
