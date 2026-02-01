<?php

namespace App\Http\Resources;

use App\Enums\TripType;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingTripResource extends JsonResource
{
	public function toArray(Request $request): array
	{
		// 1. استخراج العلاقات والبيانات المساعدة
		// نستخدم bookingTrip (camelCase) للوصول للعلاقة المحملة في الموديل
		$bookingTrip = $this->bookingTrip;
		$trip = $this->trip;

		// جلب تفاصيل الأطفال من العلاقة bookingTrip بناءً على الـ JSON
		$childrenBreakdown = $bookingTrip ? $bookingTrip->children_breakdown : [];

		$totalTravelers = $this->adults_count + $this->children_count;

		// تجهيز قائمة تفصيل أسعار الأطفال باستخدام الدالة المساعدة
		$childrenPricingList = $this->formatChildrenBreakdown($childrenBreakdown);

		return [
			// --- 1. معلومات الحجز الأساسية ---
			'id' => $this->id,
			'booking_number' => $this->booking_number,
			'status' => [
				'value' => $this->status->value ?? 'pending',
				'title' => $this->status->title() ?? $this->status->value,
				'color' => method_exists($this->status, 'color') ? $this->status->color() : 'gray',
			],
			'user' => [
				// تأكد من عمل load('user') في الكنترولر وإلا ستكون null
				'name' => $this->user->name ?? 'N/A',
				'full_phone' => $this->user->full_phone ?? 'N/A',
			],
			'dates' => [
				'check_in' => $this->check_in,
				'check_out' => $this->check_out,
				'nights_count' => $this->nights_count,
			],
			'counts' => [
				'adults' => $this->adults_count,
				'children' => $this->children_count,
				'total_travelers' => $totalTravelers,
			],
			'notes' => $this->notes,

			// --- 2. المعلومات المالية (Pricing Summary) ---
			'financials' => [
				'currency' => strtoupper($this->currency),
				'base_price' => (float)$this->price,
				'total_price' => (float)$this->total_price,

				// تفاصيل البالغين (من جدول booking_trips)
				'adults_total' => $bookingTrip ? (float)$bookingTrip->adults_price : 0,
				'adult_price_per_person' => (float)$this->price,

				// تفاصيل الأطفال
				'children_breakdown' => [
					'has_children' => $this->children_count > 0,
					'total_children_price' => $childrenBreakdown['total_children_price'] ?? ($bookingTrip->children_price ?? 0),
					'items' => $childrenPricingList,
				],

				// نص طريقة الحساب
				'calculation_note' => $this->getCalculationNote($trip, $totalTravelers),
			],

			// --- 3. معلومات الرحلة (Trip Information) ---
			// ملاحظة: يجب عمل load('trip') في الكنترولر لظهور هذه البيانات
			'trip' => $trip ? [
				'id' => $trip->id,
				'name' => $trip->name,
				'description' => $trip->program ?? $trip->description, // حسب اسم العمود في الداتا بيز
				'is_flexible' => $trip->type->value === 'flexible', // تأكدنا من النص raw value
				'type' => [
					'value' => $trip->type->value,
					'label' => __('lang.' . $trip->type->value),
				],
				'price_per_night' => $trip->type->value === 'flexible' ? (float)$this->price : null,
			] : null,

			// --- 4. بيانات المسافرين (Travelers) ---
			// يجب عمل load('travelers') في الكنترولر
			'travelers' => $this->travelers->map(function ($traveler) {
				return [
					'id' => $traveler->id,
					'full_name' => $traveler->full_name,
					'phone' => $traveler->phone_key . ' ' . $traveler->phone,
					'nationality' => $traveler->nationality,
					'age' => $traveler->age,
					'id_type' => __('lang.' . $traveler->id_type),
					'id_number' => $traveler->id_number,
				];
			}),
		];
	}

	/**
	 * تكوين نص الحساب بناء على نوع الرحلة
	 */
	protected function getCalculationNote($trip, $totalPax)
	{
		if (!$trip) return null;

		$priceFormatted = number_format((float)$this->price, 2);

		// التحقق باستخدام الـ value مباشرة أو الـ Enum
		$isFlexible = $trip->type->value === 'flexible' || $trip->type === TripType::Flexible;

		if ($isFlexible) {
			return "$totalPax × $priceFormatted × {$this->nights_count} " . __('lang.nights');
		} else {
			return "$totalPax × $priceFormatted";
		}
	}

	/**
	 * تنسيق مصفوفة الأطفال من الـ JSON المخزن
	 */
	protected function formatChildrenBreakdown(array $breakdown): array
	{
		$items = [];

		// 1. الأطفال المجان (Free Children) - يأتي مصفوفة في الـ JSON
		if (!empty($breakdown['free_children']) && is_array($breakdown['free_children'])) {
			foreach ($breakdown['free_children'] as $child) {
				$items[] = [
					'label' => __('lang.child') . ' (' . ($child['age'] ?? '?') . ' ' . __('lang.years') . ')',
					'price' => 0,
					'is_free' => true,
					'tag' => __('lang.free'),
				];
			}
		}

		// 2. الأطفال المرقمين (الأول، الثاني، الثالث) - يأتي Object أو Null
		$ordinalKeys = ['first_child' => 1, 'second_child' => 2, 'third_child' => 3];
		foreach ($ordinalKeys as $key => $number) {
			// التحقق من أن المفتاح موجود وقيمته ليست null
			if (!empty($breakdown[$key])) {
				$childData = $breakdown[$key];
				$items[] = [
					'label' => __('lang.child') . " $number (" . ($childData['age'] ?? '?') . ' ' . __('lang.years') . ')',
					'price' => (float)($childData['price'] ?? 0),
					'percentage' => (float)($childData['percentage'] ?? 0),
					'is_free' => false,
					'tag' => null,
				];
			}
		}

		// 3. الأطفال الإضافيين (Additional) - يأتي مصفوفة
		if (!empty($breakdown['additional_children']) && is_array($breakdown['additional_children'])) {
			foreach ($breakdown['additional_children'] as $index => $child) {
				$childNum = 4 + $index;
				$items[] = [
					'label' => __('lang.child') . " $childNum (" . ($child['age'] ?? '?') . ' ' . __('lang.years') . ')',
					'price' => (float)($child['price'] ?? 0),
					'percentage' => (float)($childData['percentage'] ?? 0),
					'is_free' => false,
					'tag' => null,
				];
			}
		}

		return $items;
	}
}
