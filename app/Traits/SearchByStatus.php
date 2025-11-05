<?php

namespace App\Traits;

use App\Enums\Status;

trait SearchByStatus
{
    public function scopeStatus($query, $status = Status::Active)
    {
        return $query->where('status', $status);
    }
}
