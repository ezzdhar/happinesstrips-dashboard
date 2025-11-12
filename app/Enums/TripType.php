<?php

declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

final class TripType extends Enum
{
    const Fixed = 'fixed';      // رحلة بتاريخ محدد

    const Flexible = 'flexible'; // رحلة مرنة (حسب عدد الليالي)

    public function title(): string
    {
        return match ($this->value) {
            self::Fixed => __('lang.fixed'),
            self::Flexible => __('lang.flexible'),
            default => 'Unknown',
        };
    }

    public function color(): string
    {
        return match ($this->value) {
            self::Fixed => 'green-500',
            self::Flexible => 'yellow-500',
            default => 'gray-500',
        };
    }
}
