<?php

namespace Database\Factories;

use App\Models\Completion;
use App\Models\Todo;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Completion>
 */
class CompletionFactory extends Factory
{
    protected $model = Completion::class;

    public function definition(): array
    {
        return [
            'todo_id' => Todo::factory(),
            'user_id' => User::factory(),
            'completed_at' => fake()->dateTime(),
            'note' => fake()->optional()->sentence(),
        ];
    }
}
