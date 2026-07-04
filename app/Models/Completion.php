<?php

namespace App\Models;

use App\Observers\CompletionObserver;
use Database\Factories\CompletionFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ObservedBy(CompletionObserver::class)]
class Completion extends Model
{
    /** @use HasFactory<CompletionFactory> */
    use HasFactory;

    protected $fillable = [
        'todo_id',
        'user_id',
        'completed_at',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'completed_at' => 'datetime',
        ];
    }

    public function todo(): BelongsTo
    {
        return $this->belongsTo(Todo::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
