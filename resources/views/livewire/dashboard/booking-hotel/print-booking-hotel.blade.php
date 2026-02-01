<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('lang.booking_details') }} - {{ $booking->booking_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', 'Tahoma', sans-serif;
            font-size: 9px;
            line-height: 1.3;
            color: #333;
            padding: 10px;
        }

        .print-container {
            max-width: 100%;
            margin: 0 auto;
            background: white;
        }

        /* Header Section */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding-bottom: 8px;
            border-bottom: 2px solid #2563eb;
            margin-bottom: 10px;
        }

        .logo {
            max-width: 100px;
            height: auto;
        }

        .company-info {
            text-align: right;
            flex: 1;
        }

        .company-info h1 {
            font-size: 14px;
            color: #2563eb;
            margin-bottom: 2px;
        }

        .company-info p {
            font-size: 8px;
            color: #666;
            margin: 1px 0;
        }

        /* QR Code Section */
        .qr-section {
            text-align: left;
        }

        .qr-section img {
            width: 70px;
            height: 70px;
        }

        /* Print Info */
        .print-info {
            background: #f3f4f6;
            padding: 6px 10px;
            border-radius: 4px;
            margin-bottom: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .print-info .booking-number {
            font-size: 12px;
            font-weight: bold;
            color: #2563eb;
        }

        .print-info .print-date {
            font-size: 8px;
            color: #666;
        }

        /* Status Badge */
        .status-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
            margin-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}: 8px;
        }

        .status-pending {
            background: #fef3c7;
            color: #92400e;
        }

        .status-confirmed {
            background: #d1fae5;
            color: #065f46;
        }

        .status-cancelled {
            background: #fee2e2;
            color: #991b1b;
        }

        .status-completed {
            background: #dbeafe;
            color: #1e40af;
        }

        /* Sections */
        .section {
            margin-bottom: 12px;
            page-break-inside: avoid;
        }

        .section-title {
            font-size: 11px;
            font-weight: bold;
            color: #1f2937;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 3px;
            margin-bottom: 6px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 6px;
        }

        .info-grid-2 {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 6px;
        }

        .info-item {
            padding: 4px 6px;
            background: #f9fafb;
            border-radius: 3px;
        }

        .info-label {
            font-size: 7px;
            color: #6b7280;
            text-transform: uppercase;
            margin-bottom: 2px;
            font-weight: 600;
        }

        .info-value {
            font-size: 9px;
            color: #111827;
            font-weight: 500;
        }

        .info-value.highlight {
            color: #2563eb;
            font-size: 11px;
            font-weight: bold;
        }

        /* Hotel Info Box */
        .hotel-box {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            padding: 8px;
            border-radius: 4px;
        }

        /* Table */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
        }

        table thead {
            background: #f3f4f6;
        }

        table th {
            padding: 4px 5px;
            text-align: {{ app()->getLocale() == 'ar' ? 'right' : 'left' }};
            font-size: 8px;
            font-weight: 600;
            color: #374151;
            border-bottom: 1px solid #e5e7eb;
        }

        table td {
            padding: 3px 5px;
            text-align: {{ app()->getLocale() == 'ar' ? 'right' : 'left' }};
            font-size: 8px;
            border-bottom: 1px solid #f3f4f6;
        }

        table tr:nth-child(even) {
            background: #f9fafb;
        }

        /* Footer */
        .footer {
            margin-top: 10px;
            padding-top: 8px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            font-size: 7px;
            color: #6b7280;
        }

        .footer p {
            margin: 2px 0;
        }

        /* Print Styles */
        @media print {
            body {
                padding: 0;
            }

            .print-container {
                max-width: 100%;
            }

            .no-print {
                display: none !important;
            }

            @page {
                size: A4;
                margin: 10mm;
            }

            .section {
                page-break-inside: avoid;
            }
        }

        /* RTL Support */
        [dir="rtl"] table th,
        [dir="rtl"] table td {
            text-align: right;
        }
    </style>
</head>

<body>
    <div class="print-container">
        <!-- Header -->
        <div class="header">
            <div class="company-info">
                <img src="{{ asset('logo.svg') }}" alt="Logo" class="logo">
            </div>
            <div class="qr-section">
                {!! QrCode::size(70)->generate(route('bookings.hotels.show', $booking->booking_number)) !!}
            </div>
        </div>

        <!-- Print Info -->
        <div class="print-info">
            <div>
                <div class="booking-number">{{ __('lang.booking_number') }}: {{ $booking->booking_number }}</div>
                <span class="status-badge status-{{ $booking->status->value }}">{{ $booking->status->title() }}</span>
            </div>
            <div class="print-date">
                <strong>{{ __('lang.print_date') }}:</strong> {{ now()->format('Y-m-d H:i') }}
            </div>
        </div>

        <!-- Booking Information -->
        <div class="section">
            <h2 class="section-title">{{ __('lang.booking_information') }}</h2>
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">{{ __('lang.user') }}</div>
                    <div class="info-value">{{ $booking->user->name }}</div>
                    <div style="font-size: 7px; color: #6b7280; margin-top: 1px;">
                        <span dir="ltr">{{ $booking->user->full_phone }}</span>
                    </div>
                </div>

                <div class="info-item">
                    <div class="info-label">{{ __('lang.check_in') }}</div>
                    <div class="info-value">{{ formatDate($booking->check_in) }}</div>
                </div>

                <div class="info-item">
                    <div class="info-label">{{ __('lang.check_out') }}</div>
                    <div class="info-value">{{ formatDate($booking->check_out) }}</div>
                </div>

                <div class="info-item">
                    <div class="info-label">{{ __('lang.nights') }}</div>
                    <div class="info-value">{{ $booking->nights_count }}</div>
                </div>

                <div class="info-item">
                    <div class="info-label">{{ __('lang.adults') }}</div>
                    <div class="info-value">{{ $booking->adults_count }}</div>
                </div>

                <div class="info-item">
                    <div class="info-label">{{ __('lang.children') }}</div>
                    <div class="info-value">{{ $booking->children_count }}</div>
                </div>

                <div class="info-item">
                    <div class="info-label">{{ __('lang.price') }}</div>
                    <div class="info-value">{{ $booking->price }} {{ strtoupper($booking->currency) }}</div>
                </div>

                <div class="info-item">
                    <div class="info-label">{{ __('lang.total_price') }}</div>
                    <div class="info-value highlight">{{ $booking->total_price }} {{ strtoupper($booking->currency) }}
                    </div>
                </div>
            </div>

            @if ($booking->notes)
                <div class="info-item" style="margin-top: 6px; grid-column: 1 / -1;">
                    <div class="info-label">{{ __('lang.notes') }}</div>
                    <div class="info-value">{{ $booking->notes }}</div>
                </div>
            @endif
        </div>

        <!-- Hotel Information -->
        @if ($booking->bookingHotel)
            <div class="section">
                <h2 class="section-title">{{ __('lang.hotel_information') }}</h2>
                <div class="hotel-box">
                    <div class="info-grid">
                        <div class="info-item" style="background: white;">
                            <div class="info-label">{{ __('lang.hotel') }}</div>
                            <div class="info-value">{{ $booking->bookingHotel->hotel->name }}</div>
                        </div>

                        <div class="info-item" style="background: white;">
                            <div class="info-label">{{ __('lang.room') }}</div>
                            <div class="info-value">{{ $booking->bookingHotel->room->name }}</div>
                        </div>

                        <div class="info-item" style="background: white;">
                            <div class="info-label">{{ __('lang.room_capacity') }}</div>
                            <div class="info-value">
                                {{ $booking->bookingHotel->room->adults_count }} {{ __('lang.adults') }}
                                @if ($booking->bookingHotel->room->children_count > 0)
                                    + {{ $booking->bookingHotel->room->children_count }} {{ __('lang.children') }}
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="info-item" style="margin-top: 6px; background: white; grid-column: 1 / -1;">
                        <div class="info-label">{{ __('lang.room_includes') }}</div>
                        <div class="info-value">{!! $booking->bookingHotel->room_includes !!}</div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Pricing Details -->
        @if ($booking->bookingHotel && $booking->bookingHotel->pricing_details)
            @php
                $pricing = $booking->bookingHotel->pricing_details;
            @endphp
            <div class="section">
                <h2 class="section-title">{{ __('lang.pricing_details') }}</h2>

                <!-- Adults Pricing -->
                <div
                    style="background: #f0fdf4; border: 1px solid #bbf7d0; padding: 6px; border-radius: 4px; margin-bottom: 6px;">
                    <div style="font-size: 9px; font-weight: bold; color: #166534; margin-bottom: 4px;">
                        {{ __('lang.adults') }}</div>
                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 4px;">
                        <div class="info-item" style="background: white;">
                            <div class="info-label">{{ __('lang.adults_count') }}</div>
                            <div class="info-value">{{ $pricing['adults_count'] ?? 0 }}</div>
                        </div>
                        @if (isset($pricing['adult_price_per_person']))
                            <div class="info-item" style="background: white;">
                                <div class="info-label">{{ __('lang.price_per_person') }}</div>
                                <div class="info-value">{{ number_format($pricing['adult_price_per_person'], 2) }}
                                    {{ $pricing['currency'] ?? $booking->currency }}</div>
                            </div>
                        @endif
                        <div class="info-item" style="background: white;">
                            <div class="info-label">{{ __('lang.adults_total') }}</div>
                            <div class="info-value highlight">
                                {{ number_format($booking->bookingHotel->adults_price, 2) }}
                                {{ $pricing['currency'] ?? $booking->currency }}</div>
                        </div>
                    </div>
                </div>

                <!-- Children Pricing -->
                @if (!empty($pricing['children_breakdown']) && count($pricing['children_breakdown']) > 0 && $booking->children_count)
                    <div
                        style="background: #fef3c7; border: 1px solid #fde047; padding: 6px; border-radius: 4px; margin-bottom: 6px;">
                        <div style="font-size: 9px; font-weight: bold; color: #854d0e; margin-bottom: 4px;">
                            {{ __('lang.children') }} ({{ count($pricing['children_breakdown']) }})</div>

                        @foreach ($pricing['children_breakdown'] as $child)
                            <div
                                style="background: white; padding: 4px 6px; border-radius: 3px; margin-bottom: 3px; border: 1px solid #e5e7eb;">
                                <div
                                    style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2px;">
                                    <div>
                                        <span style="font-size: 8px; font-weight: 600;">{{ __('lang.child') }}
                                            {{ $child['child_number'] }}</span>
                                        <span
                                            style="font-size: 7px; color: #6b7280; margin-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}: 4px;">{{ $child['age'] }}
                                            {{ __('lang.years') }}</span>
                                    </div>
                                    <span
                                        style="font-size: 9px; font-weight: bold; color: #854d0e;">{{ number_format($child['price'], 2) }}
                                        {{ $pricing['currency'] ?? $booking->currency }}</span>
                                </div>
                                <div style="font-size: 6px; color: #6b7280;">
                                    {{ $child['category_label'] }}
                                    @if ($child['percentage'] > 0)
                                        <span
                                            style="background: #f3f4f6; padding: 1px 4px; border-radius: 8px; margin-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}: 3px;">{{ $child['percentage'] }}%</span>
                                    @endif
                                </div>
                            </div>
                        @endforeach

                        <div
                            style="background: white; padding: 4px 6px; border-radius: 3px; margin-top: 3px; border: 1px solid #e5e7eb;">
                            <div style="display: flex; justify-content: space-between; font-weight: bold;">
                                <span style="font-size: 8px;">{{ __('lang.children_total') }}:</span>
                                <span
                                    style="font-size: 9px; color: #854d0e;">{{ number_format($booking->bookingHotel->children_price, 2) }}
                                    {{ $pricing['currency'] ?? $booking->currency }}</span>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Grand Total -->
                <div style="background: #dcfce7; border: 2px solid #22c55e; padding: 8px; border-radius: 4px;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span
                            style="font-size: 11px; font-weight: bold; color: #166534;">{{ __('lang.grand_total') }}:</span>
                        <span
                            style="font-size: 14px; font-weight: bold; color: #15803d;">{{ number_format($pricing['grand_total'], 2) }}
                            {{ $pricing['currency'] ?? $booking->currency }}</span>
                    </div>
                </div>
            </div>
        @endif

        <!-- Travelers Information -->
        @if ($booking->travelers->count() > 0)
            <div class="section">
                <h2 class="section-title">{{ __('lang.travelers') }} ({{ $booking->travelers->count() }})</h2>
                <table>
                    <thead>
                        <tr>
                            <th style="text-align: center;">#</th>
                            <th>{{ __('lang.full_name') }}</th>
                            <th>{{ __('lang.phone') }}</th>
                            <th>{{ __('lang.nationality') }}</th>
                            <th style="text-align: center;">{{ __('lang.age') }}</th>
                            <th>{{ __('lang.id_type') }}</th>
                            <th>{{ __('lang.id_number') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($booking->travelers as $traveler)
                            <tr>
                                <td style="text-align: center; font-weight: 600;">{{ $loop->iteration }}</td>
                                <td>{{ $traveler->full_name }}</td>
                                <td dir="ltr">{{ $traveler->phone_key }} {{ $traveler->phone }}</td>
                                <td>{{ $traveler->nationality }}</td>
                                <td style="text-align: center;">{{ $traveler->age }}</td>
                                <td>{{ __('lang.' . $traveler->id_type) }}</td>
                                <td>{{ $traveler->id_number }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        <!-- Footer -->
        <div class="footer">
            <p>{{ __('lang.booking_confirmation_footer') ?? __('lang.thank_you_for_booking') }}</p>
            <p style="margin-top: 2px;">{{ config('app.name') }}</p>
            <p style="margin-top: 2px;">{{ __('lang.printed_on') }}: {{ now()->format('Y-m-d H:i:s A') }}</p>
        </div>
    </div>

    <script>
        window.onload = function() {
            window.print();
        }
    </script>
</body>

</html>
