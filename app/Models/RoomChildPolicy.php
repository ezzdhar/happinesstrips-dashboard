<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoomChildPolicy extends Model
{
    protected $table = 'room_children_policies';

    protected $fillable = [
        'room_id',
        'child_number',
        'from_age',
        'to_age',
        'price_percentage',
    ];

    protected function casts(): array
    {
        return [
            'child_number' => 'integer',
            'from_age' => 'integer',
            'to_age' => 'integer',
            'price_percentage' => 'decimal:2',
        ];
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }
}
