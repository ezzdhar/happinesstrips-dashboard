<?php

namespace App\Livewire\Dashboard;

use App\Enums\Status;
use App\Models\Amenity;
use App\Models\Booking;
use App\Models\City;
use App\Models\Hotel;
use App\Models\MainCategory;
use App\Models\Room;
use App\Models\SubCategory;
use App\Models\Trip;
use App\Models\User;
use Carbon\Carbon;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('home')]
class Dashboard extends Component
{
    use WithPagination;

    public function mount(): void
    {
        view()->share('breadcrumbs', $this->breadcrumbs());
    }

    public function breadcrumbs(): array
    {
        return [
            [
                'label' => __('lang.home'),
                'icon' => 'o-home',
            ],
        ];
    }

    public function render()
    {
        // Main Statistics
        $stats = [
            'total_users' => User::count(),
            'total_main_categories' => MainCategory::count(),
            'active_main_categories' => MainCategory::where('status', Status::Active)->count(),
            'inactive_main_categories' => MainCategory::where('status', Status::Inactive)->count(),
            'total_sub_categories' => SubCategory::count(),
            'active_sub_categories' => SubCategory::where('status', Status::Active)->count(),
            'inactive_sub_categories' => SubCategory::where('status', Status::Inactive)->count(),
            'total_cities' => City::count(),
            'active_cities' => City::count(), // No status column in cities table
            'inactive_cities' => 0, // No status column in cities table
            'total_hotels' => Hotel::count(),
            'active_hotels' => Hotel::where('status', Status::Active)->count(),
            'inactive_hotels' => Hotel::where('status', Status::Inactive)->count(),
            'total_rooms' => Room::count(),
            'active_rooms' => Room::where('status', Status::Active)->count(),
            'inactive_rooms' => Room::where('status', Status::Inactive)->count(),
            'total_trips' => Trip::count(),
            'active_trips' => Trip::where('status', Status::Active)->count(),
            'inactive_trips' => Trip::where('status', Status::Inactive)->count(),
            'total_amenities' => Amenity::count(),
            'active_amenities' => Amenity::count(), // No status column in amenities table
            'inactive_amenities' => 0, // No status column in amenities table
            // Added: separated booking totals (no mixed total)
            'total_hotel_bookings' => Booking::where('type', 'hotel')->count(),
            'total_trip_bookings' => Booking::where('type', 'trip')->count(),
        ];

        // Hotel Booking Statistics by Status
        $hotelBookingStats = [
            'pending' => Booking::where('type', 'hotel')->where('status', Status::Pending)->count(),
            'under_payment' => Booking::where('type', 'hotel')->where('status', Status::UnderPayment)->count(),
            'under_cancellation' => Booking::where('type', 'hotel')->where('status', Status::UnderCancellation)->count(),
            'cancelled' => Booking::where('type', 'hotel')->where('status', Status::Cancelled)->count(),
            'completed' => Booking::where('type', 'hotel')->where('status', Status::Completed)->count(),
        ];

        // Trip Booking Statistics by Status
        $tripBookingStats = [
            'pending' => Booking::where('type', 'trip')->where('status', Status::Pending)->count(),
            'under_payment' => Booking::where('type', 'trip')->where('status', Status::UnderPayment)->count(),
            'under_cancellation' => Booking::where('type', 'trip')->where('status', Status::UnderCancellation)->count(),
            'cancelled' => Booking::where('type', 'trip')->where('status', Status::Cancelled)->count(),
            'completed' => Booking::where('type', 'trip')->where('status', Status::Completed)->count(),
        ];

        // Monthly Booking Statistics (last 6 months) - kept combined for source but charts will split
        $monthlyBookings = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $monthlyBookings[] = [
                'month' => $date->format('M Y'),
                'hotel_bookings' => Booking::where('type', 'hotel')
                    ->whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->count(),
                'trip_bookings' => Booking::where('type', 'trip')
                    ->whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->count(),
            ];
        }

        // Recent Bookings
        $recentBookings = Booking::with(['user', 'bookingHotel.hotel', 'trip'])
            ->latest()
            ->limit(5)
            ->get();

        // Top Hotels by Bookings
        $topHotels = Hotel::withCount('bookingHotels')
            ->having('booking_hotels_count', '>', 0)
            ->orderBy('booking_hotels_count', 'desc')
            ->limit(5)
            ->get();

        // Top Trips by Bookings
        $topTrips = Trip::withCount('bookings')
            ->having('bookings_count', '>', 0)
            ->orderBy('bookings_count', 'desc')
            ->limit(5)
            ->get();

        return view('livewire.dashboard.dashboard', compact(
            'stats',
            'hotelBookingStats',
            'tripBookingStats',
            'monthlyBookings',
            'recentBookings',
            'topHotels',
            'topTrips'
        ));
    }
}
