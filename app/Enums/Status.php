<?php

declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

final class Status extends Enum
{
	const Active = 'active';

	const Inactive = 'inactive';

	const Expired = 'expired';

	const Pending = 'pending';

	const Paid = 'paid';

	const InProgress = 'in_progress';

	const Completed = 'completed';

	const Closed = 'closed';
	const Start = 'start';
	const End = 'end';

	public function title(): string
	{
		return match ($this->value) {
			self::Active => __('lang.active'),
			self::Inactive => __('lang.inactive'),
			self::Expired => __('lang.expired'),
			self::Pending => __('lang.pending'),
			self::Paid => __('lang.paid'),
			self::InProgress => __('lang.in_progress'),
			self::Completed => __('lang.completed'),
			self::Closed => __('lang.closed'),
			self::Start => __('lang.start'),
			self::End => __('lang.end'),
			default => 'Unknown',
		};
	}

	public function color(): string
	{
		return match ($this->value) {
			self::Active => 'green-500',
			self::Inactive => 'red-500',
			self::Expired => 'yellow-500',
			self::Pending => 'yellow-500',
			self::Paid => 'blue-500',
			self::InProgress => 'yellow-500',
			self::Completed => 'green-500',
			self::Closed => 'red-500',
			self::Start => 'blue-500',
			self::End => 'gray-500',
			default => 'gray-500',
		};
	}
}
