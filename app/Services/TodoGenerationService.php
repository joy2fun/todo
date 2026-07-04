<?php

namespace App\Services;

use App\Models\Habit;
use App\Models\Todo;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class TodoGenerationService
{
    public function generateTodosForUser(User $user, ?Carbon $date = null): Collection
    {
        $date ??= Carbon::today();
        $dayOfWeek = $date->dayOfWeek;

        return $user->habits()
            ->where('is_active', true)
            ->get()
            ->filter(fn (Habit $habit) => $habit->isScheduledForDay($dayOfWeek))
            ->map(fn (Habit $habit) => $this->getOrCreateTodo($habit, $user, $date));
    }

    public function getOrCreateTodo(Habit $habit, User $user, ?Carbon $date = null): Todo
    {
        $date ??= Carbon::today();

        return Todo::firstOrCreate(
            [
                'habit_id' => $habit->id,
                'due_date' => $date->startOfDay(),
            ],
            [
                'user_id' => $user->id,
                'target_count' => $habit->target_count,
                'status' => 'pending',
            ]
        );
    }

    public function getTodaysTodos(User $user): Collection
    {
        return $this->generateTodosForUser($user);
    }
}
