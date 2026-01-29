<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoomPricePeriod extends Model
{
    protected $fillable = [
        'room_id',
        'currency',
        'start_date',
        'end_date',
        'price',
    ];

    public $incrementing = true;

    protected $primaryKey = 'id';

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'price' => 'decimal:2',
    ];

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * Scope للفلترة حسب العملة
     */
    public function scopeEgp($query)
    {
        return $query->where('currency', 'egp');
    }

    public function scopeUsd($query)
    {
        return $query->where('currency', 'usd');
    }

    /**
     * Scope للبحث عن الفترات التي تشمل تاريخ معين
     */
    public function scopeForDate($query, $date)
    {
        return $query->whereDate('start_date', '<=', $date)
            ->whereDate('end_date', '>=', $date);
    }

    /**
     * Scope للبحث عن الفترات التي تتقاطع مع نطاق تواريخ
     */
    public function scopeOverlappingWith($query, $startDate, $endDate)
    {
        return $query->where(function ($q) use ($startDate, $endDate) {
            $q->whereBetween('start_date', [$startDate, $endDate])
                ->orWhereBetween('end_date', [$startDate, $endDate])
                ->orWhere(function ($q2) use ($startDate, $endDate) {
                    $q2->where('start_date', '<=', $startDate)
                        ->where('end_date', '>=', $endDate);
                });
        });
    }
}
