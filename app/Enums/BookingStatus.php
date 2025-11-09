<?php declare(strict_types=1);

namespace App\Enums;

enum BookingStatus: string
{
    case Pending = 'pending';     // في انتظار التأكيد
    case Confirmed = 'confirmed';  // مؤكد
    case Cancelled = 'cancelled';  // ملغي
    case Completed = 'completed';  // مكتمل
}
