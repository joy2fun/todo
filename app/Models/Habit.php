<?php

namespace App\Models;

use Database\Factories\HabitFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Habit extends Model
{
    /** @use HasFactory<HabitFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'target_count',
        'unit',
        'schedule_days',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'schedule_days' => 'array',
            'target_count' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function todos(): HasMany
    {
        return $this->hasMany(Todo::class);
    }

    public function isScheduledForDay(int $dayOfWeek): bool
    {
        return in_array($dayOfWeek, $this->schedule_days, true);
    }
}
