<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title','Taxi Booking Management System')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm"><div class="container">
    <a class="navbar-brand fw-semibold" href="{{ route('dashboard') }}">Taxi Booking Management</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav"><span class="navbar-toggler-icon"></span></button>
    <div class="collapse navbar-collapse" id="mainNav"><div class="navbar-nav ms-auto">
        <a class="nav-link" href="{{ route('dashboard') }}">Dashboard</a><a class="nav-link" href="{{ route('passengers.index') }}">Passengers</a><a class="nav-link" href="{{ route('drivers.index') }}">Drivers</a><a class="nav-link" href="{{ route('vehicles.index') }}">Vehicles</a><a class="nav-link" href="{{ route('bookings.index') }}">Bookings</a><a class="nav-link" href="{{ route('payments.index') }}">Payments</a>
    </div></div>
</div></nav>
<main class="container py-4">
@if(session('success'))<div class="alert alert-success alert-dismissible fade show" role="alert">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif
@if(session('error'))<div class="alert alert-danger alert-dismissible fade show" role="alert">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif
@if($errors->any())<div class="alert alert-danger"><div class="fw-bold mb-1">Please correct the following:</div><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
@yield('content')
</main>
<footer class="bg-dark text-light py-3 mt-5"><div class="container text-center"><span class="small">Taxi Booking Management System — Passengers, Drivers, Vehicles, Bookings, and Payments.</span></div></footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body></html>
