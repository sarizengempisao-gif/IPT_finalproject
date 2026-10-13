# Taxi Booking Management System

Laravel final project for managing taxi bookings. The application manages passengers, drivers, vehicles, bookings, and payments.

## Exactly five database tables

1. **passengers** — Stores passenger information (name, phone, email, address)
2. **drivers** — Stores driver information (name, email, license number, phone, status)
3. **vehicles** — Stores vehicle information (plate number, type, model, year, color, capacity, status)
4. **bookings** — Stores booking records (passenger, driver, vehicle, pickup/dropoff locations, date/time, fare, status)
5. **payments** — Stores payment records (booking, reference, amount, method, date, status)

## Main relationships

- Passenger **hasMany** Bookings
- Driver **hasMany** Bookings
- Vehicle **hasMany** Bookings
- Booking **belongsTo** Passenger, Driver, and Vehicle
- Booking **hasMany** Payments
- Payment **belongsTo** Booking

## Setup

```bash
composer install
copy .env.example .env
php artisan key:generate
php artisan migrate --force
php artisan db:seed --force
php artisan serve
```

## Testing

The application uses PHPUnit with an in-memory SQLite database for testing:

```bash
php artisan test
```

## Available status values

### Driver status
- `Available` — Ready for new bookings
- `On Trip` — Currently engaged in a booking
- `Inactive` — Not available for bookings

### Vehicle status
- `Available` — Ready for assignments
- `In Use` — Currently assigned to a booking
- `Unavailable` — In maintenance or not in service

### Booking status
- `Pending` — Newly created, awaiting confirmation
- `Confirmed` — Driver and vehicle assigned
- `Completed` — Trip finished and paid
- `Cancelled` — Booking was cancelled

### Payment status
- `Paid` — Payment completed
- `Pending` — Payment awaiting completion
- `Refunded` — Payment was refunded

## Booking availability rules

- Drivers with status `On Trip` or `Inactive` cannot be assigned to new bookings
- Vehicles with status `In Use` or `Unavailable` cannot be assigned to new bookings
- No driver or vehicle can be assigned to multiple active (Confirmed/In Progress) bookings simultaneously
- When a booking is created or updated with status `Confirmed`, the driver and vehicle availability is automatically updated

## Delete restrictions

- Passengers, drivers, and vehicles cannot be deleted if they have linked bookings
- Payments are automatically deleted when their associated booking is deleted (cascade)

Bootstrap 5.3 is loaded through CDN. Do not commit `.env`; use `.env.example`.
