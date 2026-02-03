<?php

namespace App\Http\Resources;

use App\Services\FileService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingHotelResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // 1. تجهيز المتغيرات والعلاقات
        $bookingHotel = $this->bookingHotel;
        $hotel = $bookingHotel ? $bookingHotel->hotel : null;
        $room = $bookingHotel ? $bookingHotel->room : null;
        $pricing = $bookingHotel ? $bookingHotel->pricing_details : []; // مصفوفة تفاصيل السعر من الـ JSON

        // تفاصيل الأطفال تأتي كمصفوفة جاهزة في الفنادق عكس الرحلات
        $childrenBreakdown = $bookingHotel ? $bookingHotel->children_breakdown : [];

        $totalTravelers = $this->adults_count + $this->children_count;

        return [
            // --- 1. معلومات الحجز الأساسية ---
            'id' => $this->id,
            'booking_number' => $this->booking_number,
            'is_special' => (bool) $this->is_special,
            'status' => [
                'value' => $this->status->value,
                'title' => $this->status->title(),
                'color' => method_exists($this->status, 'color') ? $this->status->color() : 'primary',
            ],
            'user' => [
                'name' => $this->user->name ?? 'N/A',
                'full_phone' => $this->user->full_phone ?? 'N/A',
            ],
            'dates' => [
                'check_in' => $this->check_in, // يمكن استخدام formatDate($this->check_in)
                'check_out' => $this->check_out,
                'nights_count' => $this->nights_count,
            ],
            'counts' => [
                'adults' => $this->adults_count,
                'children' => $this->children_count,
                'total_travelers' => $totalTravelers,
            ],
            'notes' => $this->notes,

            // --- 2. المعلومات المالية (Financials) ---
            'financials' => [
                'currency' => strtoupper($this->currency),
                'base_price' => (float) $this->price,
                'total_price' => (float) $this->total_price,

                // تفاصيل البالغين
                'adults_total' => $bookingHotel ? (float) $bookingHotel->adults_price : 0,
                'adult_price_per_person' => $pricing['adult_price_per_person'] ?? 0,

                // تفاصيل الأطفال
                'children_breakdown' => [
                    'has_children' => $this->children_count > 0,
                    'total_children_price' => $bookingHotel ? (float) $bookingHotel->children_price : 0,
                    'items' => $this->formatChildrenBreakdown($childrenBreakdown),
                ],

                // نص طريقة الحساب (Calculation Info)
                'calculation_note' => $this->getCalculationNote($pricing),
            ],
            // --- 3. معلومات الفندق والغرفة (Hotel Info) ---
            'hotel_information' => $hotel ? [
                'hotel_id' => $hotel->id,
                'hotel_name' => $hotel->name, // Laravel Translatable يعيد النص حسب اللغة الحالية
                'city' => $hotel->city->name ?? null, // تأكد من تحميل علاقة City إذا أردت الاسم
                'rating' => $hotel->rating,
                'address' => $hotel->address,
                'main_image' => $hotel->files->first() ? FileService::get($hotel->files->first()->path) : null,
                // تفاصيل الغرفة
                'room_name' => $room->name ?? null,
                'room_includes' => AmenityResource::collection($room->amenities),
                'room_capacity' => [
                    'adults' => $room->adults_count ?? 0,
                    'children' => $room->children_count ?? 0,
                ],
            ] : null,

            // --- 4. بيانات المسافرين (Travelers) ---
            'travelers' => $this->travelers->map(function ($traveler) {
                return [
                    'id' => $traveler->id,
                    'full_name' => $traveler->full_name,
                    'phone' => $traveler->phone_key . ' ' . $traveler->phone,
                    'nationality' => $traveler->nationality,
                    'age' => $traveler->age,
                    'id_type' => __('lang.' . $traveler->id_type),
                    'id_number' => $traveler->id_number,
                    'type' => $traveler->type,
                    'type_label' => __('lang.' . $traveler->type),
                ];
            }),
        ];
    }

    /**
     * تنسيق قائمة الأطفال
     * في الفنادق تأتي مصفوفة جاهزة ولكن نعيد تسمية المفاتيح لتوحيد الواجهة الأمامية
     */
    protected function formatChildrenBreakdown(?array $breakdown): array
    {
        if (empty($breakdown) || ! is_array($breakdown)) {
            return [];
        }

        return collect($breakdown)->map(function ($child) {
            return [
                'label' => $child['category_label'] ?? (__('lang.child') . ' ' . ($child['child_number'] ?? '')),
                'age' => $child['age'] ?? 0,
                'price' => (float) ($child['price'] ?? 0),
                'percentage' => $child['percentage'] ?? 0,
                'is_free' => ($child['category'] ?? '') === 'free' || ($child['price'] == 0),
                'tag' => ($child['category'] ?? '') === 'free' ? __('lang.free') : ($child['percentage'] . '%'),
            ];
        })->toArray();
    }

    /**
     * جملة توضيح الحساب
     * (عدد البالغين * سعر الفرد * عدد الليالي)
     */
    protected function getCalculationNote($pricing)
    {
        $adultsCount = $this->adults_count;
        $nights = $this->nights_count;
        $pricePerPerson = number_format($pricing['adult_price_per_person'] ?? 0, 2);

        // مثال: 2 بالغين × 800.00 × 3 ليالي
        return "$adultsCount " . __('lang.adults') . " × $pricePerPerson × $nights " . __('lang.nights');
    }
}
