<?php declare(strict_types=1);

namespace App\Enums;

enum TripType: string
{
    case Fixed = 'fixed';      // رحلة بتاريخ محدد
    case Flexible = 'flexible'; // رحلة مرنة (حسب عدد الليالي)
}

