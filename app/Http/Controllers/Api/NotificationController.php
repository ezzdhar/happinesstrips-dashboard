<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\NotificationRequest;
use App\Http\Resources\NotificationResource;
use App\Models\User;
use App\Notifications\UserNotification;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
	use ApiResponse;

	public function index()
	{
//        auth()->user()->unreadNotifications->markAsRead();
		$notifications = auth()->user()->notifications()->paginate(request()->query('limit', 10));
		$data = NotificationResource::collection($notifications);
		return $this->responseOk(__('lang.success'), $data,true);
	}

	public function read(NotificationRequest $request)
	{
		$notification = auth()->user()->notifications()->where('id', $request->notification_id)->first();
		$notification->markAsRead();
		return $this->responseCreated(__('lang.success'));
	}

	public function readAll()
	{
		auth()->user()->unreadNotifications->markAsRead();
		return $this->responseCreated(__('lang.success'));
	}

	public function delete(NotificationRequest $request)
	{
		$notification = auth()->user()->notifications()->where('id', $request->notification_id)->first();
		$notification->delete();
		return $this->responseCreated(__('lang.success'));
	}

	public function unreadNotificationCount()
	{
		$data['count'] = auth()->user()->unreadNotifications->count();
		return $this->responseOk(__('lang.success'), $data);
	}

	public function send(Request $request)
	{
		$user = User::find(auth()->id());
		$rand = rand(1, 100);
		$data = [
			'id' => $rand,
			'type' => fake()->randomElement(['booking_trip', 'booking_hotel']),
		];
		$title = [
			'en' => 'New Trip Booking Pending Confirmation Booking Number' . $rand,
			'ar' => 'حجز رحلة جديد في انتظار التأكيد رقم الحجز ' . $rand,
		];
		$body = [
			'en' => 'Your booking for is pending confirmation. We will notify you once it is confirmed.' . $rand,
			'ar' => 'حجزك في  في انتظار التأكيد. سنقوم بإبلاغك بمجرد تأكيده.' . $rand,
		];
		$user->notify(new UserNotification($title, $body, $data, auth()->user()->language));
		return $this->responseOk(__('lang.success'), $data);
	}

}
