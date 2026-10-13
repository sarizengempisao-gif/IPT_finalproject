<?php

namespace App\Http\Controllers;

use App\Models\Passenger;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PassengerController extends Controller
{
    public function index()
    {
        return view('passengers.index', ['passengers' => Passenger::withCount('bookings')->orderBy('full_name')->get()]);
    }

    public function create()
    {
        return view('passengers.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate(['full_name' => 'required|string|max:150', 'phone' => 'required|string|max:30', 'email' => 'nullable|email|max:150|unique:passengers,email', 'address' => 'required|string|max:255']);
        Passenger::create($data);

        return to_route('passengers.index')->with('success', 'Passenger created successfully.');
    }

    public function show(Passenger $passenger)
    {
        $passenger->load(['bookings.driver', 'bookings.vehicle', 'bookings.payments']);

        return view('passengers.show', compact('passenger'));
    }

    public function edit(Passenger $passenger)
    {
        return view('passengers.edit', compact('passenger'));
    }

    public function update(Request $request, Passenger $passenger)
    {
        $data = $request->validate(['full_name' => 'required|string|max:150', 'phone' => 'required|string|max:30', 'email' => ['nullable', 'email', 'max:150', Rule::unique('passengers', 'email')->ignore($passenger->passenger_id, 'passenger_id')], 'address' => 'required|string|max:255']);
        $passenger->update($data);

        return to_route('passengers.index')->with('success', 'Passenger updated successfully.');
    }

    public function destroy(Passenger $passenger)
    {
        if ($passenger->bookings()->exists()) {
            return back()->with('error', 'This passenger cannot be deleted because booking records are linked to the account.');
        } $passenger->delete();

        return to_route('passengers.index')->with('success', 'Passenger deleted successfully.');
    }
}
