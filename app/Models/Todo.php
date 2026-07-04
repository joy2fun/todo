<?php

namespace App\Models;

use Database\Factories\TodoFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Todo extends Model
{
    /** @use HasFactory<TodoFactory> */
    use HasFactory;

    protected $fillable = [
        'habit_id',
        'user_id',
        'due_date',
        'target_count',
        'completed_count',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'target_count' => 'integer',
            'completed_count' => 'integer',
        ];
    }

    public function habit(): BelongsTo
    {
        return $this->belongsTo(Habit::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function completions(): HasMany
    {
        return $this->hasMany(Completion::class);
    }

    public function isCompleted(): bool
    {
        return $this->completed_count >= $this->target_count;
    }

    public function markCompleted(): void
    {
        $this->update(['status' => 'completed']);
    }

    public function incrementCompletedCount(): void
    {
        $this->increment('completed_count');

        if ($this->isCompleted()) {
            $this->markCompleted();
        }
    }
}
