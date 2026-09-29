<?php

namespace Database\Factories;

use App\Models\Endpoint;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Endpoint>
 */
class EndpointFactory extends Factory
{
    protected $model = Endpoint::class;

    public function definition(): array
    {
        return [
            'method' => 'GET',
            'path' => 'api/'.fake()->unique()->slug(2),
            'status_code' => 200,
            'content_type' => 'application/json',
            'body' => '{}',
            'headers' => [],
            'note' => fake()->sentence(),
            'is_active' => true,
        ];
    }

    public function method(string $method): static
    {
        return $this->state(fn (): array => ['method' => $method]);
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }

    public function json(string $path, mixed $body, int $statusCode = 200): static
    {
        return $this->state(fn (): array => [
            'path' => $path,
            'status_code' => $statusCode,
            'content_type' => 'application/json',
            'body' => json_encode($body, JSON_PRETTY_PRINT),
        ]);
    }
}
