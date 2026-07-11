<?php

namespace Tests\Feature;

use App\Filament\Widgets\HabitsOverview;
use App\Models\Habit;
use App\Models\Todo;
use App\Models\User;
use Carbon\Carbon;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class HabitsOverviewWidgetTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        Filament::auth()->login($this->user);
    }

    public function test_daily_habit_streak_counts_consecutive_days(): void
    {
        $habit = Habit::factory()->create([
            'user_id' => $this->user->id,
            'schedule_days' => [0, 1, 2, 3, 4, 5, 6],
            'target_count' => 1,
        ]);

        Todo::factory()->completed()->create([
            'habit_id' => $habit->id,
            'user_id' => $this->user->id,
            'due_date' => Carbon::today(),
        ]);
        Todo::factory()->completed()->create([
            'habit_id' => $habit->id,
            'user_id' => $this->user->id,
            'due_date' => Carbon::yesterday(),
        ]);
        Todo::factory()->completed()->create([
            'habit_id' => $habit->id,
            'user_id' => $this->user->id,
            'due_date' => Carbon::today()->subDays(2),
        ]);

        Livewire::test(HabitsOverview::class)
            ->assertSee('3 days');
    }

    public function test_non_daily_habit_streak_counts_consecutive_scheduled_days(): void
    {
        $habit = Habit::factory()->create([
            'user_id' => $this->user->id,
            'schedule_days' => [1, 3, 5], // Mon, Wed, Fri
            'target_count' => 1,
        ]);

        // Today is Saturday 2026-07-11, so the last scheduled days were:
        // Friday 2026-07-10, Wednesday 2026-07-08, Monday 2026-07-06
        Todo::factory()->completed()->create([
            'habit_id' => $habit->id,
            'user_id' => $this->user->id,
            'due_date' => Carbon::parse('2026-07-10'),
        ]);
        Todo::factory()->completed()->create([
            'habit_id' => $habit->id,
            'user_id' => $this->user->id,
            'due_date' => Carbon::parse('2026-07-08'),
        ]);
        Todo::factory()->completed()->create([
            'habit_id' => $habit->id,
            'user_id' => $this->user->id,
            'due_date' => Carbon::parse('2026-07-06'),
        ]);

        Livewire::test(HabitsOverview::class)
            ->assertSee('3 days');
    }

    public function test_streak_breaks_when_scheduled_day_is_missed(): void
    {
        $habit = Habit::factory()->create([
            'user_id' => $this->user->id,
            'schedule_days' => [1, 3, 5], // Mon, Wed, Fri
            'target_count' => 1,
        ]);

        // Completed Fri and Mon but missed Wed
        Todo::factory()->completed()->create([
            'habit_id' => $habit->id,
            'user_id' => $this->user->id,
            'due_date' => Carbon::parse('2026-07-10'), // Friday
        ]);
        Todo::factory()->completed()->create([
            'habit_id' => $habit->id,
            'user_id' => $this->user->id,
            'due_date' => Carbon::parse('2026-07-06'), // Monday
        ]);

        Livewire::test(HabitsOverview::class)
            ->assertSee('1 days');
    }

    public function test_streak_is_zero_with_no_completed_todos(): void
    {
        Habit::factory()->create([
            'user_id' => $this->user->id,
            'schedule_days' => [0, 1, 2, 3, 4, 5, 6],
            'target_count' => 1,
        ]);

        Livewire::test(HabitsOverview::class)
            ->assertSee('0 days');
    }

    public function test_streak_only_counts_completed_todos(): void
    {
        $habit = Habit::factory()->create([
            'user_id' => $this->user->id,
            'schedule_days' => [0, 1, 2, 3, 4, 5, 6],
            'target_count' => 2,
        ]);

        // Completed (target reached)
        Todo::factory()->create([
            'habit_id' => $habit->id,
            'user_id' => $this->user->id,
            'due_date' => Carbon::today(),
            'target_count' => 2,
            'completed_count' => 2,
        ]);

        // Not completed (target not reached)
        Todo::factory()->create([
            'habit_id' => $habit->id,
            'user_id' => $this->user->id,
            'due_date' => Carbon::yesterday(),
            'target_count' => 2,
            'completed_count' => 1,
        ]);

        Livewire::test(HabitsOverview::class)
            ->assertSee('1 days');
    }
}
