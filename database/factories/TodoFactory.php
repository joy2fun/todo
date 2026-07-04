<?php

namespace Database\Factories;

use App\Models\Habit;
use App\Models\Todo;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Todo>
 */
class TodoFactory extends Factory
{
    protected $model = Todo::class;

    public function definition(): array
    {
        return [
            'habit_id' => Habit::factory(),
            'user_id' => User::factory(),
            'due_date' => fake()->date(),
            'target_count' => fake()->numberBetween(1, 10),
            'completed_count' => 0,
            'status' => 'pending',
        ];
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'completed_count' => $attributes['target_count'],
            'status' => 'completed',
        ]);
    }

    public function skipped(): static
    {
        return $this->state(fn () => ['status' => 'skipped']);
    }
}
