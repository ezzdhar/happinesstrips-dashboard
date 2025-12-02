<?php

namespace App\Http\Resources;

use App\Services\FileService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingHotelResource extends JsonResource
{
	public function toArray(Request $request): array
	{
		// تجهيز المتغيرات لتسهيل القراءة وتجنب الأخطاء إذا كانت القيمة null
		$bookingHotel = $this->bookingHotel;
		$pricing = $bookingHotel ? $bookingHotel->pricing_details : [];
		$hotel = $bookingHotel ? $bookingHotel->hotel : null;
		$room = $bookingHotel ? $bookingHotel->room : null;

		return [
			'id' => $this->id,
			'is_special' => $this->is_special,
			'booking_number' => $this->booking_number,
			'status' => [
				'value' => $this->status->value,
				'title' => $this->status->title(),
			],
			'user' => [
				'name' => $this->user->name,
				'full_phone' => $this->user->full_phone,
			],
			'dates' => [
				'check_in' =>formatDate($this->check_in),
				'check_out' => formatDate($this->check_out) ,
				'nights_count' => $this->nights_count,
			],
			'counts' => [
				'adults' => $this->adults_count,
				'children' => $this->children_count,
			],
			'notes' => $this->notes,

			// 2. المعلومات المالية العامة
			'financials' => [
				'base_price' => $this->price,
				'total_price' => $this->total_price,
				'currency' => $this->currency,
			],

			// 3. معلومات الفندق والغرفة
			'hotel_information' => $hotel ? [
				'hotel_name' => $hotel->name,
				'city' => $hotel->city->name ?? null,
				'room_name' => $room->name ?? null,
				'room_capacity' => [
					'adults' => $room->adults_count ?? 0,
					'children' => $room->children_count ?? 0,
				],
				'room_includes' => $bookingHotel->room_includes, // يتم إرجاعها كـ HTML أو نص حسب التخزين
				'main_image' => $hotel->files->first() ? FileService::get($hotel->files->first()->path) : null,
			] : null,

			// 4. تفاصيل التسعير الدقيقة (Pricing Details)
			'pricing_details' => $bookingHotel ? [
				'currency' => $pricing['currency'] ?? $this->currency,
				'grand_total' => $pricing['grand_total'] ?? 0,

				// تفاصيل البالغين
				'adults' => [
					'count' => $pricing['adults_count'] ?? 0,
					'price_per_person' => $pricing['adult_price_per_person'] ?? 0,
					'total_price' => $bookingHotel->adults_price,
				],

				// تفاصيل الأطفال
				'children' => [
					'has_children' => !empty($pricing['children_breakdown']),
					'total_price' => $bookingHotel->children_price,
					'breakdown' => collect($pricing['children_breakdown'] ?? [])->map(function($child) {
						return [
							'child_number' => $child['child_number'],
							'age' => $child['age'],
							'price' => $child['price'],
							'category_label' => $child['category_label'],
							'percentage' => $child['percentage'],
						];
					}),
				],
			] : null,

			// 5. بيانات المسافرين (Travelers)
			'travelers' => $this->travelers->map(function($traveler) {
				return [
					'id' => $traveler->id,
					'full_name' => $traveler->full_name,
					'phone' => $traveler->phone_key . ' ' . $traveler->phone, // دمج المفتاح مع الرقم
					'nationality' => $traveler->nationality,
					'age' => $traveler->age,
					'id_type' => $traveler->id_type, // يمكن ترجمتها هنا باستخدام __('lang.'.$traveler->id_type)
					'id_number' => $traveler->id_number,
					'type' => $traveler->type, // adult or child
					'type_label' => __('lang.' . $traveler->type), // للتسهيل على الفرونت إند
				];
			}),
		];
	}
}