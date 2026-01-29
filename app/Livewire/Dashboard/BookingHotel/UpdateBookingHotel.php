<?php

namespace App\Livewire\Dashboard\BookingHotel;

use App\Enums\Status;
use App\Models\Booking;
use App\Models\Hotel;
use App\Models\Room;
use App\Models\User;
use App\Services\Booking\HotelBookingService;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('update_hotel_booking')]
class UpdateBookingHotel extends Component
{
	public Booking $booking;

	// خصائص الحجز الأساسية
	public $user_id;
	public $hotel_id;
	public $room_id;
	public $selected_room;
	public $check_in;
	public $check_out;
	public $notes;
	public $currency;
	public $nights_count = 1;

	// خصائص الأعداد والأسعار (تمت إضافتها لتطابق الإضافة)
	public $adults_count = 1;
	public $children_ages = [];
	public $pricing_result = null;

	// القوائم والبيانات
	public $travelers = [];
	public $hotels = [];
	public $users = [];
	public $rooms = [];

	public function mount(Booking $booking): void
	{
		$this->booking = $booking->load(['bookingHotel', 'travelers']);

		// تعبئة البيانات الأساسية
		$this->user_id = $booking->user_id;
		$this->check_in = $booking->check_in?->format('Y-m-d');
		$this->check_out = $booking->check_out?->format('Y-m-d');
		$this->nights_count = $booking->nights_count;
		$this->notes = $booking->notes;
		$this->currency = strtolower($booking->currency);
		$this->adults_count = $booking->adults_count;

		// استخراج أعمار الأطفال من المسافرين الحاليين
		// نفترض أن نوع المسافر 'child' هو المحدد
		$this->children_ages = $booking->travelers->where('type', 'child')->pluck('age')->toArray();

		// تحميل بيانات الفندق والغرفة
		if ($booking->bookingHotel) {
			$this->hotel_id = $booking->bookingHotel->hotel_id;
			$this->room_id = $booking->bookingHotel->room_id;
			$this->selected_room = $booking->bookingHotel->room;
		}

		// تحميل المسافرين للواجهة (مع الحفاظ على الـ ID للتعديل)
		foreach ($booking->travelers as $traveler) {
			$this->travelers[] = [
				'id' => $traveler->id, // مهم جداً: نحتفظ بالـ ID للتعديل بدلاً من الحذف والإنشاء
				'full_name' => $traveler->full_name,
				'phone_key' => $traveler->phone_key,
				'phone' => $traveler->phone,
				'nationality' => $traveler->nationality,
				'age' => $traveler->age,
				'id_type' => $traveler->id_type,
				'id_number' => $traveler->id_number,
				'type' => $traveler->type,
			];
		}

		// تحميل القوائم المنسدلة
		$this->hotels = Hotel::status(Status::Active)->get()->map(function ($hotel) {
			return [
				'id' => $hotel->id,
				'name' => $hotel->name,
			];
		})->toArray();
		$this->users = User::role('user')->get(['id', 'name', 'phone'])->toArray();

		if ($this->hotel_id) {
			$this->rooms = Room::where('hotel_id', $this->hotel_id)->status(Status::Active)->get();
		}

		// حساب السعر المبدئي لعرضه
		$this->calculatePrice(false); // false تعني لا تعدل المسافرين في الـ mount

		view()->share('breadcrumbs', $this->breadcrumbs());
	}

	public function breadcrumbs(): array
	{
		return [
			[
				'label' => __('lang.hotel_bookings'),
				'icon' => 'o-calendar',
				'link' => route('bookings.hotels'),
			],
			[
				'label' => __('lang.update_hotel_booking') . ' - ' . $this->booking->booking_number,
			],
		];
	}

	// --- أحداث التغيير (Update Events) ---

	public function updatedHotelId(): void
	{
		$this->room_id = null;
		$this->selected_room = null;
		$this->rooms = [];
		// لا نصفر المسافرين هنا في التعديل لتجنب فقدان البيانات بالخطأ، لكن يمكن فعل ذلك حسب الرغبة
		if ($this->hotel_id) {
			$this->rooms = Room::where('hotel_id', $this->hotel_id)->status(Status::Active)->get();
		}
	}

	public function updatedRoomId(): void
	{
		if ($this->room_id) {
			$room = Room::find($this->room_id);
			if ($room) {
				$this->selected_room = $room;
				// عند تغيير الغرفة، نتأكد أن العدد الحالي لا يتجاوز سعة الغرفة الجديدة
				if ($this->adults_count > $room->adults_count) {
					$this->adults_count = $room->adults_count;
				}
				$this->calculatePrice();
			}
		}
	}

	public function updatedCheckIn(): void
	{
		if ($this->check_in && $this->check_out) {
			$checkIn = Carbon::parse($this->check_in);
			$checkOut = Carbon::parse($this->check_out);
			$this->nights_count = $checkIn->diffInDays($checkOut);
		}
		$this->calculatePrice();
	}

	public function updatedCheckOut(): void
	{
		$this->updatedCheckIn();
	}

	public function updatedCurrency(): void
	{
		$this->calculatePrice();
	}

	public function updatedAdultsCount(): void
	{
		if ($this->selected_room && $this->selected_room->adults_count < $this->adults_count) {
			$this->adults_count = $this->selected_room->adults_count;
			flash()->error(__('lang.adults_count_exceeded_maximum', ['max' => $this->selected_room->adults_count]));
			return;
		}
		$this->calculatePrice();
	}

	public function updatedChildrenAges(): void
	{
		$this->calculatePrice();
	}

	// --- إدارة الأطفال ---

	public function addChild(): void
	{
		if ($this->selected_room) {
			// التحقق من أن مجموع الأفراد لا يتجاوز سعة الغرفة الإجمالية
			$totalCapacity = $this->selected_room->adults_count + $this->selected_room->children_count;
			$currentTotal = $this->adults_count + count($this->children_ages);

			if ($currentTotal >= $totalCapacity) {
				flash()->error(__('lang.total_guests_exceeded_maximum', ['max' => $totalCapacity]));
				return;
			}
		}
		$this->children_ages[] = 0;
		$this->calculatePrice();
	}

	public function removeChild($index): void
	{
		unset($this->children_ages[$index]);
		$this->children_ages = array_values($this->children_ages);
		$this->calculatePrice();
	}

	// --- المنطق الأساسي (Core Logic) ---

	public function calculatePrice($syncTravelers = true): void
	{
		if (!$this->room_id || !$this->check_in || !$this->check_out) {
			$this->pricing_result = null;
			return;
		}

		$room = Room::find($this->room_id);
		if (!$room) return;
		$this->selected_room = $room;

		// استخدام دالة الموديل لحساب السعر
		$this->pricing_result = $room->calculateBookingPrice(
			checkIn: $this->check_in,
			checkOut: $this->check_out,
			adultsCount: (int)$this->adults_count,
			childrenAges: $this->children_ages,
			currency: $this->currency
		);

		if ($this->pricing_result['success']) {
			$this->nights_count = $this->pricing_result['nights_count'];
			if ($syncTravelers) {
				$this->syncTravelersArray();
			}
		}
	}

	/**
	 * دالة ذكية لمزامنة مصفوفة المسافرين عند تغيير العدد
	 * تحافظ على البيانات المدخلة وتضيف/تحذف حسب الحاجة
	 */
	public function syncTravelersArray(): void
	{
		$totalRequired = $this->adults_count + count($this->children_ages);
		$currentCount = count($this->travelers);

		// Adjust array size
		if ($currentCount < $totalRequired) {
			for ($i = $currentCount; $i < $totalRequired; $i++) {
				$this->travelers[] = $this->getEmptyTraveler();
			}
		} elseif ($currentCount > $totalRequired) {
			$this->travelers = array_slice($this->travelers, 0, $totalRequired);
		}

		// Update children ages if applicable (optional, maybe mapped by index?)
		// Since we don't distinguish explicitly, we just ensure we have enough slots.
		// The user can fill names. Age is manual input in traveler form too?
		// In CreateBooking, age comes from children_ages for children, but traveler has its own age input.
		// Let's just ensure size matches.
	}

	private function getEmptyTraveler(): array
	{
		return [
			'id' => null,
			'full_name' => '',
			'phone_key' => '+20',
			'phone' => '',
			'nationality' => '',
			'age' => '',
			'id_type' => 'passport',
			'id_number' => '',
			// 'type' => 'adult', // Removed
		];
	}

	// --- الحفظ (Update) ---

	public function rules(): array
	{
		return [
			'user_id' => 'required|exists:users,id',
			'hotel_id' => 'required|exists:hotels,id',
			'room_id' => 'required|exists:rooms,id',
			'check_in' => 'required|date',
			'check_out' => 'required|date|after:check_in',
			'nights_count' => 'required|integer|min:1',
			'currency' => 'required|in:egp,usd',
			'notes' => 'nullable|string',

			// تحقق من الأعداد والأعمار
			'adults_count' => 'required|integer|min:1',
			'children_ages' => 'nullable|array',
			'children_ages.*' => 'required|integer|min:0|max:18',

			// تحقق من المسافرين
			'travelers' => 'required|array|min:1',
			'travelers.*.full_name' => 'required|string',
			'travelers.*.phone_key' => 'required|string',
			'travelers.*.phone' => 'required|string',
			'travelers.*.nationality' => 'required|string',
			'travelers.*.age' => 'required|integer|min:1',
			'travelers.*.id_type' => 'required|in:passport,national_id',
			'travelers.*.id_number' => 'required|string',
		];
	}

	public function update(HotelBookingService $bookingService): void
	{
		$this->validate();

		try {
			$data = [
				'user_id' => $this->user_id,
				'hotel_id' => $this->hotel_id,
				'room_id' => $this->room_id,
				'check_in' => $this->check_in,
				'check_out' => $this->check_out,
				'currency' => $this->currency,
				'notes' => $this->notes,
				'travelers' => $this->travelers,
				// البيانات الديناميكية الجديدة
				'adults_count' => $this->adults_count,
				'children_ages' => $this->children_ages,
			];

			$bookingService->updateBooking($this->booking, $data);

			flash()->success(__('lang.updated_successfully', ['attribute' => __('lang.booking')]));
			$this->redirectIntended(default: route('bookings.hotels.show', $this->booking->id));
		} catch (\Exception $e) {
			flash()->error($e->getMessage());
		}
	}

	public function render(): View
	{
		return view('livewire.dashboard.booking-hotel.update-booking-hotel');
	}
}
