<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatFaq extends Model
{
    use HasFactory;

    protected $guarded = ['id', 'created_at', 'updated_at'];

    protected function casts(): array
    {
        return [
            'tags' => 'array',
        ];
    }

    /**
     * Increment the usage count for this FAQ.
     */
    public function incrementUsage(): void
    {
        $this->increment('usage_count');
    }

    /**
     * Scope a query to search FAQs by keyword.
     */
    public function scopeSearch($query, ?string $keyword)
    {
        if (! $keyword) {
            return $query;
        }

        return $query->where(function ($q) use ($keyword) {
            $q->where('question', 'like', "%{$keyword}%")
                ->orWhere('answer', 'like', "%{$keyword}%");
        });
    }

    /**
     * Scope a query to filter FAQs by tags.
     */
    public function scopeWithTags($query, array $tags)
    {
        return $query->whereJsonContains('tags', $tags);
    }

    /**
     * Scope a query to order by most used.
     */
    public function scopePopular($query)
    {
        return $query->orderByDesc('usage_count');
    }
}

