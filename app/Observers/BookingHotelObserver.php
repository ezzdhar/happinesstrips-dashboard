<?php

namespace App\Observers;

use App\Models\BookingHotel;
use App\Models\User;
use App\Notifications\UserNotification;

class BookingHotelObserver
{

	public function created(BookingHotel $bookingHotel): void
	{
		$title = [
			'en' => 'New Hotel Booking Pending Confirmation Booking Number ' . $bookingHotel->booking->booking_number,
			'ar' => 'حجز فندق جديد في انتظار التأكيد رقم الحجز ' . $bookingHotel->booking->booking_number,
		];
		$body = [
			'en' => 'Your booking for ' . $bookingHotel->hotel->name . ' is pending confirmation. We will notify you once it is confirmed.',
			'ar' => 'حجزك في ' . $bookingHotel->hotel->name . ' في انتظار التأكيد. سنقوم بإبلاغك بمجرد تأكيده.',
		];
		$this->sendNotification($title, $body, $bookingHotel);
	}

	public function updated(BookingHotel $bookingHotel): void
	{
		//Status::Pending,Status::UnderPayment,Status::UnderCancellation,Status::Cancelled,Status::Completed
		if ($bookingHotel->isDirty('status')) {
			$status = $bookingHotel->status->value;
			$title = [
				'en' => 'Hotel Booking Status Updated Booking Number ' . $bookingHotel->booking->booking_number,
				'ar' => 'تم تحديث حالة حجز الفندق رقم الحجز ' . $bookingHotel->booking->booking_number,
			];
			$body = [
				'en' => 'Your booking for ' . $bookingHotel->hotel->name . ' status has been updated to ' . __("lang.ar.$status") . '.',
				'ar' => 'تم تحديث حالة حجزك في ' . $bookingHotel->hotel->name . ' إلى ' . __("lang.en.$status") . '.',
			];
			$this->sendNotification($title, $body, $bookingHotel);
		}
	}


	//send notification
	private function sendNotification(array $title, array $body, BookingHotel $bookingHotel): void
	{
		$user = User::find($bookingHotel->booking->user_id);
		$data = [
			'id' => $bookingHotel->booking_id,
			'type' => 'booking_hotel',
		];
		$user->notify(new UserNotification($title, $body, $data, $user->language));
	}
}
