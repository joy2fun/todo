<?php

namespace Database\Factories;

use App\Models\Habit;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Habit>
 */
class HabitFactory extends Factory
{
    protected $model = Habit::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->word(),
            'description' => fake()->sentence(),
            'target_count' => fake()->numberBetween(1, 10),
            'unit' => fake()->randomElement(['glasses', 'reps', 'minutes', 'pages', 'sets']),
            'schedule_days' => [0, 1, 2, 3, 4, 5, 6],
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }

    public function daily(): static
    {
        return $this->state(fn () => [
            'schedule_days' => [0, 1, 2, 3, 4, 5, 6],
            'target_count' => fake()->numberBetween(1, 10),
        ]);
    }

    public function weekdays(): static
    {
        return $this->state(fn () => ['schedule_days' => [1, 2, 3, 4, 5]]);
    }
}
