<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CalculateBookingRoomPriceRequest;
use App\Http\Requests\Api\GetRoomDetailsRequest;
use App\Http\Requests\Api\GetRoomRequest;
use App\Http\Resources\RoomResource;
use App\Http\Resources\RoomSimpleResource;
use App\Models\Room;
use App\Traits\ApiResponse;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class RoomController extends Controller
{
    use ApiResponse;

    public function rooms(GetRoomRequest $request)
    {
        $adultsCount = (int) $request->adults_count;
        $childrenCount = (int) ($request->children_count ?? 0);
        $totalGuests = $adultsCount + $childrenCount;

        // Base query مع الفلاتر الأساسية
        $baseQuery = Room::query()
            ->filter($request->name)
            ->hotelId($request->hotel_id)
            ->when(filter_var($request->is_featured, FILTER_VALIDATE_BOOLEAN) == 1, function (Builder $query) {
                return $query->where('is_featured', 1);
            })
            ->isAvailableRangeCovered()
            ->with(['amenities', 'childrenPolicies']);

        // أولاً: البحث عن تطابق تام (نفس عدد البالغين + عدد أطفال كافي)
        $exactMatchQuery = clone $baseQuery;
        $exactMatchQuery->where('adults_count', $adultsCount)
            ->where('children_count', '>=', $childrenCount);

        $exactMatchCount = $exactMatchQuery->count();

        if ($exactMatchCount > 0) {
            // يوجد تطابق تام - استخدام هذه الغرف
            $rooms = $exactMatchQuery
                ->filterByCalculatedPrice()
                ->paginate($request->per_page ?? 15);
        } else {
            // لم يوجد تطابق تام - البحث عن غرف بسعة كافية
            // الشروط:
            // 1. مجموع (البالغين + الأطفال) للغرفة >= مجموع الطلب
            // 2. عدد البالغين في الغرفة >= عدد البالغين المطلوبين
            $rooms = (clone $baseQuery)
                ->where('adults_count', '>=', $adultsCount)
                ->whereRaw('(adults_count + children_count) >= ?', [$totalGuests])
                ->filterByCalculatedPrice()
                ->paginate($request->per_page ?? 15);
        }

        return $this->responseOk(message: __('lang.rooms'), data: RoomSimpleResource::collection($rooms));
    }

    public function roomDetails(GetRoomDetailsRequest $request, Room $room)
    {
        return $this->responseOk(message: __('lang.room_details'), data: new RoomResource($room));
    }

    public function calculateBookingRoomPrice(CalculateBookingRoomPriceRequest $request, Room $room)
    {
        $calculate_booking_price = $room->calculateBookingPrice(
            checkIn: Carbon::parse($request->start_date),
            checkOut: Carbon::parse($request->end_date),
            adultsCount: $request->adults_count,
            childrenAges: $request->children_ages ?? [],
            currency: $request->attributes->get('currency', 'egp')
        );
        if (! $calculate_booking_price['success']) {
            return $this->responseError(message: $calculate_booking_price['error']);
        }
        $room = new RoomResource(resource: $room);
        $data = array_merge($calculate_booking_price, ['room' => $room]);
        return $this->responseOk(message: __('lang.calculate_booking_room_price'), data: $data);
    }
}
