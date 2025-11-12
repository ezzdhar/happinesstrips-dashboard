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

    const Confirmed = 'confirmed';  // مؤكد

    const Cancelled = 'cancelled';  // ملغي

    const UnderPayment = 'under_payment';

    const UnderCancellation = 'under_cancellation';

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
            self::Confirmed => __('lang.confirmed'),
            self::Cancelled => __('lang.cancelled'),
            self::UnderPayment => __('lang.under_payment'),
            self::UnderCancellation => __('lang.under_cancellation'),
            default => 'Unknown',
        };
    }

    public function color(): string
    {
        return match ($this->value) {
            self::Active => 'green-500',
            self::Inactive => 'gray-500',
            self::Expired => 'rose-500',
            self::Pending => 'amber-500',
            self::Paid => 'blue-500',
            self::InProgress => 'indigo-500',
            self::Completed => 'teal-500',
            self::Closed => 'red-600',
            self::Start => 'cyan-500',
            self::End => 'slate-500',
            self::Confirmed => 'emerald-500',
            self::Cancelled => 'red-500',
            self::UnderPayment => 'orange-500',
            self::UnderCancellation => 'purple-500',
            default => 'zinc-500',
        };
    }
}
