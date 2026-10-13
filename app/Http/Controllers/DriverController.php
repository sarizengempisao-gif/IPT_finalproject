<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DriverController extends Controller
{
    public function index()
    {
        return view('drivers.index', ['drivers' => Driver::withCount('bookings')->orderBy('full_name')->get()]);
    }

    public function create()
    {
        return view('drivers.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate(['full_name' => 'required|string|max:150', 'email' => 'nullable|email|max:150|unique:drivers,email', 'license_no' => 'required|string|max:50|unique:drivers,license_no', 'phone' => 'required|string|max:30', 'status' => 'required|in:Available,On Trip,Inactive']);
        Driver::create($data);

        return to_route('drivers.index')->with('success', 'Driver created successfully.');
    }

    public function show(Driver $driver)
    {
        $driver->load(['bookings.passenger', 'bookings.vehicle', 'bookings.payments']);

        return view('drivers.show', compact('driver'));
    }

    public function edit(Driver $driver)
    {
        return view('drivers.edit', compact('driver'));
    }

    public function update(Request $request, Driver $driver)
    {
        $data = $request->validate(['full_name' => 'required|string|max:150', 'email' => ['nullable', 'email', 'max:150', Rule::unique('drivers', 'email')->ignore($driver->driver_id, 'driver_id')], 'license_no' => ['required', 'string', 'max:50', Rule::unique('drivers', 'license_no')->ignore($driver->driver_id, 'driver_id')], 'phone' => 'required|string|max:30', 'status' => 'required|in:Available,On Trip,Inactive']);
        $driver->update($data);

        return to_route('drivers.index')->with('success', 'Driver updated successfully.');
    }

    public function destroy(Driver $driver)
    {
        if ($driver->bookings()->exists()) {
            return back()->with('error', 'This driver cannot be deleted because booking records are linked to the driver.');
        } $driver->delete();

        return to_route('drivers.index')->with('success', 'Driver deleted successfully.');
    }
}
