<?php

namespace App\Observers;

use App\Jobs\SendAdminNotificationJob;
use App\Models\Booking;
use App\Models\User;
use App\Notifications\UserNotification;

class BookingObserver
{
	public function created(Booking $booking): void
	{
		if ($booking->trip_id) {
			$title = [
				'en' => 'New Trip Booking Pending Confirmation Booking Number ' . $booking->booking_number,
				'ar' => 'حجز رحلة جديد في انتظار التأكيد رقم الحجز ' . $booking->booking_number,
			];
			$body = [
				'en' => 'Your booking for ' . $booking->trip->getTranslation('name', 'en') . ' is pending confirmation. We will notify you once it is confirmed.',
				'ar' => 'حجزك في ' . $booking->trip->getTranslation('name', 'ar') . ' في انتظار التأكيد. سنقوم بإبلاغك بمجرد تأكيده.',
			];
			$this->sendUserNotification($title, $body, $booking);
			if ($booking->createdBy?->hasRole('user')) {
				$title = [
					'en' => 'New Trip Booking Pending Confirmation Booking Number ' . $booking->booking_number,
					'ar' => 'حجز رحلة جديد في انتظار التأكيد رقم الحجز ' . $booking->booking_number,
				];
				$body = [
					'en' => 'A new booking for ' . $booking->trip->getTranslation('name', 'en') . ' has been made by ' . $booking->createdBy->name . '. Please review and confirm the booking.',
					'ar' => 'تم إجراء حجز جديد لـ ' . $booking->trip->getTranslation('name', 'ar') . ' بواسطة ' . $booking->createdBy->name . '. يرجى مراجعة وتأكيد الحجز.',
				];
				SendAdminNotificationJob::dispatch(title: $title, body: $body, permission: 'show_booking_trip', url: route('bookings.trips.show', $booking->id));
			}
		}
	}

	public function updated(Booking $booking): void
	{
		//Status::Pending,Status::UnderPayment,Status::UnderCancellation,Status::Cancelled,Status::Completed
		if ($booking->isDirty('status')) {
			$status = $booking->status->value;
			if ($booking->trip_id) {
				$title = [
					'en' => 'Trip Booking Status Updated Booking Number ' . $booking->booking_number,
					'ar' => 'تم تحديث حالة حجز الرحلة رقم الحجز ' . $booking->booking_number,
				];
				$body = [
					'en' => 'Your booking for ' . $booking->trip->getTranslation('name', 'en') . ' status has been updated to ' . __("lang.en.$status") . '.',
					'ar' => 'تم تحديث حالة حجزك في ' . $booking->trip->getTranslation('name', 'ar') . ' إلى ' . __("lang.ar.$status") . '.',
				];
				$this->sendUserNotification($title, $body, $booking);
			}
		}
	}

	private function sendUserNotification(array $title, array $body, Booking $booking): void
	{
		$user = User::find($booking->user_id);
		$data = ['id' => $booking->id, 'type' => 'booking_trip'];
		$user->notify(new UserNotification($title, $body, $data, $user->language));
	}
}
