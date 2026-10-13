<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class VehicleController extends Controller
{
    public function index()
    {
        return view('vehicles.index', ['vehicles' => Vehicle::withCount('bookings')->orderBy('plate_number')->get()]);
    }

    public function create()
    {
        return view('vehicles.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate(['plate_number' => 'required|string|max:30|unique:vehicles,plate_number', 'vehicle_type' => 'required|string|max:80', 'model' => 'required|string|max:100', 'year' => 'required|integer|min:1900|max:'.(date('Y') + 1), 'color' => 'required|string|max:40', 'capacity' => 'required|integer|min:1|max:20', 'status' => 'required|in:Available,In Service,Unavailable']);
        Vehicle::create($data);

        return to_route('vehicles.index')->with('success', 'Vehicle created successfully.');
    }

    public function show(Vehicle $vehicle)
    {
        $vehicle->load(['bookings.passenger', 'bookings.driver', 'bookings.payments']);

        return view('vehicles.show', compact('vehicle'));
    }

    public function edit(Vehicle $vehicle)
    {
        return view('vehicles.edit', compact('vehicle'));
    }

    public function update(Request $request, Vehicle $vehicle)
    {
        $data = $request->validate(['plate_number' => ['required', 'string', 'max:30', Rule::unique('vehicles', 'plate_number')->ignore($vehicle->vehicle_id, 'vehicle_id')], 'vehicle_type' => 'required|string|max:80', 'model' => 'required|string|max:100', 'year' => 'required|integer|min:1900|max:'.(date('Y') + 1), 'color' => 'required|string|max:40', 'capacity' => 'required|integer|min:1|max:20', 'status' => 'required|in:Available,In Service,Unavailable']);
        $vehicle->update($data);

        return to_route('vehicles.index')->with('success', 'Vehicle updated successfully.');
    }

    public function destroy(Vehicle $vehicle)
    {
        if ($vehicle->bookings()->exists()) {
            return back()->with('error', 'This vehicle cannot be deleted because booking records are linked to it.');
        } $vehicle->delete();

        return to_route('vehicles.index')->with('success', 'Vehicle deleted successfully.');
    }
}
