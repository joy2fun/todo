<?php

namespace Tests\Feature;

use App\Models\Completion;
use App\Models\Habit;
use App\Models\Todo;
use App\Models\User;
use App\Services\TodoGenerationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HabitTrackingTest extends TestCase
{
    use RefreshDatabase;

    public function test_habit_can_be_created(): void
    {
        $user = User::factory()->create();

        $habit = Habit::factory()->create([
            'user_id' => $user->id,
            'name' => 'Drink Water',
            'target_count' => 8,
            'unit' => 'glasses',
            'schedule_days' => [0, 1, 2, 3, 4, 5, 6],
        ]);

        $this->assertDatabaseHas('habits', [
            'name' => 'Drink Water',
            'target_count' => 8,
            'unit' => 'glasses',
        ]);
    }

    public function test_habit_is_scheduled_for_day(): void
    {
        $habit = Habit::factory()->create([
            'schedule_days' => [1, 4], // Monday, Thursday
        ]);

        $this->assertTrue($habit->isScheduledForDay(1)); // Monday
        $this->assertTrue($habit->isScheduledForDay(4)); // Thursday
        $this->assertFalse($habit->isScheduledForDay(0)); // Sunday
        $this->assertFalse($habit->isScheduledForDay(2)); // Tuesday
    }

    public function test_todo_generation_creates_todo_for_scheduled_day(): void
    {
        $user = User::factory()->create();
        $habit = Habit::factory()->create([
            'user_id' => $user->id,
            'schedule_days' => [0, 1, 2, 3, 4, 5, 6], // All days
            'target_count' => 5,
        ]);

        $service = app(TodoGenerationService::class);
        $todos = $service->generateTodosForUser($user);

        $this->assertCount(1, $todos);
        $this->assertEquals($habit->id, $todos->first()->habit_id);
        $this->assertEquals(5, $todos->first()->target_count);
    }

    public function test_todo_generation_skips_inactive_habits(): void
    {
        $user = User::factory()->create();
        $habit = Habit::factory()->inactive()->create([
            'user_id' => $user->id,
            'schedule_days' => [0, 1, 2, 3, 4, 5, 6],
        ]);

        $service = app(TodoGenerationService::class);
        $todos = $service->generateTodosForUser($user);

        $this->assertCount(0, $todos);
    }

    public function test_todo_does_not_duplicate(): void
    {
        $user = User::factory()->create();
        $habit = Habit::factory()->daily()->create([
            'user_id' => $user->id,
        ]);

        $service = app(TodoGenerationService::class);
        $todos1 = $service->generateTodosForUser($user);
        $todos2 = $service->generateTodosForUser($user);

        $this->assertEquals($todos1->first()->id, $todos2->first()->id);
        $this->assertDatabaseCount('todos', 1);
    }

    public function test_observer_updates_count_on_create(): void
    {
        $user = User::factory()->create();
        $habit = Habit::factory()->create([
            'user_id' => $user->id,
            'target_count' => 3,
        ]);

        $service = app(TodoGenerationService::class);
        $todo = $service->getOrCreateTodo($habit, $user);

        Completion::factory()->create([
            'todo_id' => $todo->id,
            'user_id' => $user->id,
        ]);

        $this->assertEquals(1, $todo->fresh()->completed_count);
        $this->assertEquals('pending', $todo->fresh()->status);
    }

    public function test_observer_marks_completed_when_target_reached(): void
    {
        $user = User::factory()->create();
        $habit = Habit::factory()->create([
            'user_id' => $user->id,
            'target_count' => 2,
        ]);

        $service = app(TodoGenerationService::class);
        $todo = $service->getOrCreateTodo($habit, $user);

        Completion::factory()->create([
            'todo_id' => $todo->id,
            'user_id' => $user->id,
        ]);

        $this->assertEquals(1, $todo->fresh()->completed_count);
        $this->assertEquals('pending', $todo->fresh()->status);

        Completion::factory()->create([
            'todo_id' => $todo->id,
            'user_id' => $user->id,
        ]);

        $this->assertEquals(2, $todo->fresh()->completed_count);
        $this->assertEquals('completed', $todo->fresh()->status);
    }

    public function test_observer_updates_count_on_delete(): void
    {
        $user = User::factory()->create();
        $habit = Habit::factory()->create([
            'user_id' => $user->id,
            'target_count' => 3,
        ]);

        $service = app(TodoGenerationService::class);
        $todo = $service->getOrCreateTodo($habit, $user);

        $completion = Completion::factory()->create([
            'todo_id' => $todo->id,
            'user_id' => $user->id,
        ]);

        $this->assertEquals(1, $todo->fresh()->completed_count);

        $completion->delete();

        $this->assertEquals(0, $todo->fresh()->completed_count);
        $this->assertEquals('pending', $todo->fresh()->status);
    }

    public function test_completion_record_is_created(): void
    {
        $user = User::factory()->create();
        $habit = Habit::factory()->create([
            'user_id' => $user->id,
            'target_count' => 5,
        ]);

        $service = app(TodoGenerationService::class);
        $todo = $service->getOrCreateTodo($habit, $user);

        $completion = Completion::factory()->create([
            'todo_id' => $todo->id,
            'user_id' => $user->id,
            'completed_at' => now(),
            'note' => 'Morning glass',
        ]);

        $this->assertDatabaseHas('completions', [
            'todo_id' => $todo->id,
            'note' => 'Morning glass',
        ]);
    }

    public function test_user_has_habits_relationship(): void
    {
        $user = User::factory()->create();
        $habit = Habit::factory()->create(['user_id' => $user->id]);

        $this->assertCount(1, $user->habits);
        $this->assertEquals($habit->id, $user->habits->first()->id);
    }

    public function test_user_has_todos_relationship(): void
    {
        $user = User::factory()->create();
        $todo = Todo::factory()->create(['user_id' => $user->id]);

        $this->assertCount(1, $user->todos);
        $this->assertEquals($todo->id, $user->todos->first()->id);
    }

    public function test_user_has_completions_relationship(): void
    {
        $user = User::factory()->create();
        $completion = Completion::factory()->create(['user_id' => $user->id]);

        $this->assertCount(1, $user->completions);
        $this->assertEquals($completion->id, $user->completions->first()->id);
    }
}
