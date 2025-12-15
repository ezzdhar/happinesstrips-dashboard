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
        $rooms = Room::query()
            ->filter($request->name)
            ->hotelId($request->hotel_id)
            ->when($request->is_featured, function (Builder $query) use ($request) {
                $isFeatured = filter_var($request->is_featured, FILTER_VALIDATE_BOOLEAN);

                return $query->where('is_featured', $isFeatured ? 1 : 0);
            })
            ->where('adults_count', (int) $request->adults_count)
            ->when($request->children_count, fn ($q) => $q->where('children_count', $request->children_count))
            ->isAvailableRangeCovered()
            ->filterByCalculatedPrice()
            ->with(['amenities'])
            ->paginate($request->per_page ?? 15);

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
