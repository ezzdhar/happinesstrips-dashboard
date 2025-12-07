<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChatMessage extends Model
{
    use HasFactory;

    protected $guarded = ['id', 'created_at', 'updated_at'];

    protected function casts(): array
    {
        return [
            'meta' => 'array',
        ];
    }

    /**
     * Get the user that owns the message.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the feedback for this message.
     */
    public function feedback(): HasMany
    {
        return $this->hasMany(ChatFeedback::class);
    }

    /**
     * Scope a query to only include messages for a specific session.
     */
    public function scopeForSession($query, string $sessionId)
    {
        return $query->where('session_id', $sessionId);
    }

    /**
     * Scope a query to only include messages for a specific user.
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope a query to only include messages with a specific role.
     */
    public function scopeByRole($query, string $role)
    {
        return $query->where('role', $role);
    }

    /**
     * Scope a query to only include messages with a specific status.
     */
    public function scopeWithStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Mark the message as sent.
     */
    public function markAsSent(?string $externalId = null): void
    {
        $this->update([
            'status' => 'sent',
            'external_id' => $externalId,
        ]);
    }

    /**
     * Mark the message as failed.
     */
    public function markAsFailed(array $errorDetails = []): void
    {
        $meta = $this->meta ?? [];
        $meta['error'] = $errorDetails;
        $meta['failed_at'] = now()->toIso8601String();

        $this->update([
            'status' => 'failed',
            'meta' => $meta,
        ]);
    }
}

