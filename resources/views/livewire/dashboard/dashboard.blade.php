<div>
	<div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl">
		<!-- Main Statistics Section -->
		<div class="grid auto-rows-min gap-4 grid-cols-2 sm:grid-cols-3 md:grid-cols-5">
			<div class="relative h-auto overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
				<x-stat title="{{ __('lang.users') }}" value="{{ $stats['total_users'] }}" icon="o-users" tooltip="{{ __('lang.total_users') }}" color="text-blue-500"/>
			</div>
			<div class="relative h-auto overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
				<x-stat title="{{ __('lang.hotels') }}" value="{{ $stats['total_hotels'] }}" icon="o-building-office" tooltip="{{ __('lang.active') }}: {{ $stats['active_hotels'] }}" color="text-purple-500"/>
			</div>
			<div class="relative h-auto overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
				<x-stat title="{{ __('lang.trips') }}" value="{{ $stats['total_trips'] }}" icon="o-rocket-launch" tooltip="{{ __('lang.active') }}: {{ $stats['active_trips'] }}" color="text-orange-500"/>
			</div>
			<div class="relative h-auto overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
				<x-stat title="{{ __('lang.hotel_bookings') }}" value="{{ $stats['total_hotel_bookings'] }}" icon="o-building-office-2" tooltip="{{ __('lang.total_hotel_bookings') }}" color="text-indigo-500"/>
			</div>
			<div class="relative h-auto overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
				<x-stat title="{{ __('lang.trip_bookings') }}" value="{{ $stats['total_trip_bookings'] }}" icon="o-map" tooltip="{{ __('lang.total_trip_bookings') }}" color="text-pink-500"/>
			</div>
		</div>

		<!-- Hotel Booking Status Statistics -->
		<div class="bg-white dark:bg-gray-800 rounded-xl border border-neutral-200 dark:border-neutral-700 p-4">
			<h3 class="text-lg font-semibold mb-4 flex items-center gap-2">
				<x-icon name="o-building-office" class="w-5 h-5 text-purple-500"/>
				{{ __('lang.hotel_bookings') }}
			</h3>
			<div class="grid auto-rows-min gap-4 grid-cols-2 sm:grid-cols-3 md:grid-cols-5">
				<div class="relative h-auto overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
					<x-stat title="{{ __('lang.pending') }}" value="{{ $hotelBookingStats['pending'] }}" icon="o-clock" tooltip="{{ __('lang.pending_bookings') }}" color="text-yellow-500"/>
				</div>
				<div class="relative h-auto overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
					<x-stat title="{{ __('lang.under_payment') }}" value="{{ $hotelBookingStats['under_payment'] }}" icon="o-credit-card" tooltip="{{ __('lang.under_payment_bookings') }}" color="text-blue-500"/>
				</div>
				<div class="relative h-auto overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
					<x-stat title="{{ __('lang.under_cancellation') }}" value="{{ $hotelBookingStats['under_cancellation'] }}" icon="o-exclamation-triangle" tooltip="{{ __('lang.under_cancellation_bookings') }}" color="text-orange-500"/>
				</div>
				<div class="relative h-auto overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
					<x-stat title="{{ __('lang.cancelled') }}" value="{{ $hotelBookingStats['cancelled'] }}" icon="o-x-circle" tooltip="{{ __('lang.cancelled_bookings') }}" color="text-red-500"/>
				</div>
				<div class="relative h-auto overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
					<x-stat title="{{ __('lang.completed') }}" value="{{ $hotelBookingStats['completed'] }}" icon="o-check-circle" tooltip="{{ __('lang.completed_bookings') }}" color="text-green-600"/>
				</div>
			</div>
		</div>

		<!-- Trip Booking Status Statistics -->
		<div class="bg-white dark:bg-gray-800 rounded-xl border border-neutral-200 dark:border-neutral-700 p-4">
			<h3 class="text-lg font-semibold mb-4 flex items-center gap-2">
				<x-icon name="o-rocket-launch" class="w-5 h-5 text-orange-500"/>
				{{ __('lang.trip_bookings') }}
			</h3>
			<div class="grid auto-rows-min gap-4 grid-cols-2 sm:grid-cols-3 md:grid-cols-5">
				<div class="relative h-auto overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
					<x-stat title="{{ __('lang.pending') }}" value="{{ $tripBookingStats['pending'] }}" icon="o-clock" tooltip="{{ __('lang.pending_bookings') }}" color="text-yellow-500"/>
				</div>
				<div class="relative h-auto overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
					<x-stat title="{{ __('lang.under_payment') }}" value="{{ $tripBookingStats['under_payment'] }}" icon="o-credit-card" tooltip="{{ __('lang.under_payment_bookings') }}" color="text-blue-500"/>
				</div>
				<div class="relative h-auto overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
					<x-stat title="{{ __('lang.under_cancellation') }}" value="{{ $tripBookingStats['under_cancellation'] }}" icon="o-exclamation-triangle" tooltip="{{ __('lang.under_cancellation_bookings') }}" color="text-orange-500"/>
				</div>
				<div class="relative h-auto overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
					<x-stat title="{{ __('lang.cancelled') }}" value="{{ $tripBookingStats['cancelled'] }}" icon="o-x-circle" tooltip="{{ __('lang.cancelled_bookings') }}" color="text-red-500"/>
				</div>
				<div class="relative h-auto overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
					<x-stat title="{{ __('lang.completed') }}" value="{{ $tripBookingStats['completed'] }}" icon="o-check-circle" tooltip="{{ __('lang.completed_bookings') }}" color="text-green-600"/>
				</div>
			</div>
		</div>

		<!-- Categories, Cities & Rooms Statistics -->
		<div class="grid auto-rows-min gap-4 grid-cols-2 sm:grid-cols-3 md:grid-cols-5">
			<div class="relative h-auto overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
				<x-stat title="{{ __('lang.main_categories') }}" value="{{ $stats['total_main_categories'] }}" icon="o-rectangle-stack" tooltip="{{ __('lang.active') }}: {{ $stats['active_main_categories'] }}" color="text-teal-500"/>
			</div>
			<div class="relative h-auto overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
				<x-stat title="{{ __('lang.sub_categories') }}" value="{{ $stats['total_sub_categories'] }}" icon="o-squares-2x2" tooltip="{{ __('lang.active') }}: {{ $stats['active_sub_categories'] }}" color="text-cyan-500"/>
			</div>
			<div class="relative h-auto overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
				<x-stat title="{{ __('lang.cities') }}" value="{{ $stats['total_cities'] }}" icon="o-map" tooltip="{{ __('lang.total_cities') }}" color="text-rose-500"/>
			</div>
			<div class="relative h-auto overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
				<x-stat title="{{ __('lang.rooms') }}" value="{{ $stats['total_rooms'] }}" icon="o-home" tooltip="{{ __('lang.active') }}: {{ $stats['active_rooms'] }}" color="text-amber-500"/>
			</div>
			<div class="relative h-auto overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
				<x-stat title="{{ __('lang.amenities') }}" value="{{ $stats['total_amenities'] }}" icon="o-star" tooltip="{{ __('lang.total_amenities') }}" color="text-lime-500"/>
			</div>
		</div>

		<!-- Charts Section: Split hotel vs trip monthly charts -->
		<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
			<!-- Monthly Hotel Bookings Chart -->
			<div class="relative overflow-hidden shadow-md rounded-lg border border-neutral-200 dark:border-neutral-700">
				<div class="p-6">
					<h3 class="text-lg font-semibold mb-4 flex items-center gap-2">
						<x-icon name="o-building-office" class="w-5 h-5 text-purple-500" />
						{{ __('lang.monthly_hotel_bookings') }}
					</h3>
					<div class="h-64">
						<canvas id="monthlyHotelBookingsChart"></canvas>
					</div>
				</div>
			</div>
			<!-- Monthly Trip Bookings Chart -->
			<div class="relative overflow-hidden shadow-md rounded-lg border border-neutral-200 dark:border-neutral-700">
				<div class="p-6">
					<h3 class="text-lg font-semibold mb-4 flex items-center gap-2">
						<x-icon name="o-rocket-launch" class="w-5 h-5 text-orange-500" />
						{{ __('lang.monthly_trip_bookings') }}
					</h3>
					<div class="h-64">
						<canvas id="monthlyTripBookingsChart"></canvas>
					</div>
				</div>
			</div>
		</div>

		<!-- Booking Status Charts Section: Split hotel vs trip status -->
		<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
			<!-- Hotel Booking Status Chart -->
			<div class="relative overflow-hidden shadow-md rounded-lg border border-neutral-200 dark:border-neutral-700">
				<div class="p-6">
					<h3 class="text-lg font-semibold mb-4 flex items-center gap-2">
						<x-icon name="o-building-office" class="w-5 h-5 text-purple-500" />
						{{ __('lang.hotel_booking_status_distribution') }}
					</h3>
					<div class="h-64">
						<canvas id="hotelBookingStatusChart"></canvas>
					</div>
				</div>
			</div>
			<!-- Trip Booking Status Chart -->
			<div class="relative overflow-hidden shadow-md rounded-lg border border-neutral-200 dark:border-neutral-700">
				<div class="p-6">
					<h3 class="text-lg font-semibold mb-4 flex items-center gap-2">
						<x-icon name="o-rocket-launch" class="w-5 h-5 text-orange-500" />
						{{ __('lang.trip_booking_status_distribution') }}
					</h3>
					<div class="h-64">
						<canvas id="tripBookingStatusChart"></canvas>
					</div>
				</div>
			</div>
		</div>

		<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
			<!-- Recent Bookings Table -->
			<div class="relative overflow-hidden shadow-md rounded-lg border border-neutral-200 dark:border-neutral-700">
				<div class="p-6">
					<h3 class="text-lg font-semibold mb-4">{{ __('lang.recent_bookings') }}</h3>
					<div class="overflow-x-auto">
						<table class="table-fixed w-full">
							<thead>
								<tr>
									<td class="py-2 border border-gray-200 text-center font-bold p-4">{{ __('lang.customer') }}</td>
									<td class="py-2 border border-gray-200 text-center font-bold p-4">{{ __('lang.type') }}</td>
									<td class="py-2 border border-gray-200 text-center font-bold p-4">{{ __('lang.status') }}</td>
									<td class="py-2 border border-gray-200 text-center font-bold p-4">{{ __('lang.date') }}</td>
								</tr>
							</thead>
							<tbody>
								@forelse($recentBookings as $booking)
								<tr class="py-3">
									<td class="py-3 border border-gray-200 p-4">{{ $booking->user->name ?? 'N/A' }}</td>
									<td class="py-3 border border-gray-200 p-4">
										@if($booking->type == 'hotel')
											<span class="badge badge-primary">{{ __('lang.hotel') }}</span>
										@else
											<span class="badge badge-secondary">{{ __('lang.trip') }}</span>
										@endif
									</td>
									<td class="py-3 border border-gray-200 p-4">
										@php
											$badgeClass = match($booking->status->value) {
												'pending' => 'warning',
												'under_payment' => 'info',
												'under_cancellation' => 'warning',
												'cancelled' => 'error',
												'completed' => 'success',
												default => 'neutral'
											};
										@endphp
										<span class="badge badge-{{ $badgeClass }}">
											{{ $booking->status->title() }}
										</span>
									</td>
									<td class="py-3 border border-gray-200 p-4">{{ $booking->created_at->format('M d, Y') }}</td>
								</tr>
								@empty
								<tr>
									<td colspan="4" class="py-3 border border-gray-200 p-4 text-center text-gray-500">{{ __('lang.no_bookings') }}</td>
								</tr>
								@endforelse
							</tbody>
						</table>
					</div>
				</div>
			</div>

			<!-- Top Performing Items -->
			<div class="relative overflow-hidden shadow-md rounded-lg border border-neutral-200 dark:border-neutral-700">
				<div class="p-6">
					<h3 class="text-lg font-semibold mb-4">{{ __('lang.top_performing') }}</h3>

					<div class="mb-6">
						<h4 class="font-medium mb-2">{{ __('lang.top_hotels') }}</h4>
						<div class="space-y-2">
							@forelse($topHotels as $hotel)
							<div class="flex justify-between items-center p-2 bg-gray-200 dark:bg-gray-800 rounded">
								<span>{{ $hotel->name }}</span>
								<span class="badge badge-success">{{ $hotel->booking_hotels_count }} {{ __('lang.bookings') }}</span>
							</div>
							@empty
							<p class="text-gray-500 text-sm">{{ __('lang.no_data_available') }}</p>
							@endforelse
						</div>
					</div>

					<div>
						<h4 class="font-medium mb-2">{{ __('lang.top_trips') }}</h4>
						<div class="space-y-2">
							@forelse($topTrips as $trip)
							<div class="flex justify-between items-center p-2 bg-gray-200 dark:bg-gray-800 rounded">
								<span>{{ $trip->name }}</span>
								<span class="badge badge-info">{{ $trip->bookings_count }} {{ __('lang.bookings') }}</span>
							</div>
							@empty
							<p class="text-gray-500 text-sm">{{ __('lang.no_data_available') }}</p>
							@endforelse
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- Chart.js Scripts -->
	<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
	<script>
		document.addEventListener('DOMContentLoaded', function() {
			// Monthly Hotel Bookings Chart
			new Chart(document.getElementById('monthlyHotelBookingsChart').getContext('2d'), {
				type: 'bar',
				data: {
					labels: {!! json_encode(array_column($monthlyBookings, 'month')) !!},
					datasets: [{
						label: '{{ __("lang.hotel_bookings") }}',
						data: {!! json_encode(array_column($monthlyBookings, 'hotel_bookings')) !!},
						backgroundColor: 'rgba(99, 102, 241, 0.8)',
						borderColor: 'rgb(99, 102, 241)',
						borderWidth: 1
					}]
				},
				options: {
                    responsive: true,
					maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0, // يمنع الكسور
                                stepSize: 1   // يخلي التدريج بالأعداد الصحيحة فقط
                            }
                        }
                    }
                }
			});

			// Monthly Trip Bookings Chart
			new Chart(document.getElementById('monthlyTripBookingsChart').getContext('2d'), {
				type: 'bar',
				data: {
					labels: {!! json_encode(array_column($monthlyBookings, 'month')) !!},
					datasets: [{
						label: '{{ __("lang.trip_bookings") }}',
						data: {!! json_encode(array_column($monthlyBookings, 'trip_bookings')) !!},
						backgroundColor: 'rgba(236, 72, 153, 0.8)',
						borderColor: 'rgb(236, 72, 153)',
						borderWidth: 1
					}]
				},
				options: {
                    responsive: true,
					maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0, // يمنع الكسور
                                stepSize: 1   // يخلي التدريج بالأعداد الصحيحة فقط
                            }
                        }
                    }
                }
			});

			// Hotel Booking Status Chart
			new Chart(document.getElementById('hotelBookingStatusChart').getContext('2d'), {
				type: 'doughnut',
				data: {
					labels: [
						'{{ __("lang.pending") }}',
						'{{ __("lang.under_payment") }}',
						'{{ __("lang.under_cancellation") }}',
						'{{ __("lang.cancelled") }}',
						'{{ __("lang.completed") }}'
					],
					datasets: [{
						data: [
							{{ $hotelBookingStats['pending'] }},
							{{ $hotelBookingStats['under_payment'] }},
							{{ $hotelBookingStats['under_cancellation'] }},
							{{ $hotelBookingStats['cancelled'] }},
							{{ $hotelBookingStats['completed'] }}
						],
						backgroundColor: [
							'rgba(234, 179, 8, 0.8)',
							'rgba(59, 130, 246, 0.8)',
							'rgba(249, 115, 22, 0.8)',
							'rgba(239, 68, 68, 0.8)',
							'rgba(34, 197, 94, 0.8)'
						],
						borderColor: [
							'rgb(234, 179, 8)',
							'rgb(59, 130, 246)',
							'rgb(249, 115, 22)',
							'rgb(239, 68, 68)',
							'rgb(34, 197, 94)'
						],
						borderWidth: 2
					}]
				},
				options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
			});

			// Trip Booking Status Chart
			new Chart(document.getElementById('tripBookingStatusChart').getContext('2d'), {
				type: 'doughnut',
				data: {
					labels: [
						'{{ __("lang.pending") }}',
						'{{ __("lang.under_payment") }}',
						'{{ __("lang.under_cancellation") }}',
						'{{ __("lang.cancelled") }}',
						'{{ __("lang.completed") }}'
					],
					datasets: [{
						data: [
							{{ $tripBookingStats['pending'] }},
							{{ $tripBookingStats['under_payment'] }},
							{{ $tripBookingStats['under_cancellation'] }},
							{{ $tripBookingStats['cancelled'] }},
							{{ $tripBookingStats['completed'] }}
						],
						backgroundColor: [
							'rgba(234, 179, 8, 0.8)',
							'rgba(59, 130, 246, 0.8)',
							'rgba(249, 115, 22, 0.8)',
							'rgba(239, 68, 68, 0.8)',
							'rgba(34, 197, 94, 0.8)'
						],
						borderColor: [
							'rgb(234, 179, 8)',
							'rgb(59, 130, 246)',
							'rgb(249, 115, 22)',
							'rgb(239, 68, 68)',
							'rgb(34, 197, 94)'
						],
						borderWidth: 2
					}]
				},
				options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
			});
		});
	</script>
</div>