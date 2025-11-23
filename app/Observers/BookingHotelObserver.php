<?php

namespace App\Observers;

use App\Jobs\SendAdminNotificationJob;
use App\Models\BookingHotel;
use App\Models\User;
use App\Notifications\UserNotification;

class BookingHotelObserver
{

	public function created(BookingHotel $bookingHotel): void
	{
		$booking = $bookingHotel->booking;
		$title = [
			'en' => 'New Hotel Booking Pending Confirmation Booking Number ' . $booking->booking_number,
			'ar' => 'حجز فندق جديد في انتظار التأكيد رقم الحجز ' . $booking->booking_number,
		];
		$body = [
			'en' => 'Your booking for ' . $bookingHotel->hotel->getTranslation('name', 'en') . ' is pending confirmation. We will notify you once it is confirmed.',
			'ar' => 'حجزك في ' . $bookingHotel->hotel->getTranslation('name', 'ar') . ' في انتظار التأكيد. سنقوم بإبلاغك بمجرد تأكيده.',
		];
		$this->sendNotification($title, $body, $bookingHotel);
		if ($booking->createdBy?->hasRole('user')) {
			$title = [
				'en' => 'New Hotel Booking Pending Confirmation Booking Number ' . $booking->booking_number,
				'ar' => 'حجز فندق جديد في انتظار التأكيد رقم الحجز ' . $booking->booking_number,
			];
			$body = [
				'en' => 'A new booking for ' . $bookingHotel->hotel->getTranslation('name', 'en') . ' has been made by ' . $booking->createdBy->name . '. Please review and confirm the booking.',
				'ar' => 'تم إجراء حجز جديد لـ ' . $bookingHotel->hotel->getTranslation('name', 'ar') . ' بواسطة ' . $booking->createdBy->name . '. يرجى مراجعة وتأكيد الحجز.',
			];
			SendAdminNotificationJob::dispatch(title: $title, body: $body, permission: 'show_booking_hotel', url: route('bookings.hotels.show', $booking->id));
		}
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
				'en' => 'Your booking for ' . $bookingHotel->hotel->getTranslation('name', 'en') . ' status has been updated to ' . __("lang.en.$status") . '.',
				'ar' => 'تم تحديث حالة حجزك في ' . $bookingHotel->hotel->getTranslation('name', 'ar') . ' إلى ' . __("lang.ar.$status") . '.',
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
