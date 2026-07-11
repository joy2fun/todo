<?php

namespace Tests\Feature;

use App\Filament\Widgets\ContributionsChart;
use App\Models\Habit;
use App\Models\Todo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ContributionsChartTest extends TestCase
{
    use RefreshDatabase;

    public function test_widget_renders_with_no_data(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test(ContributionsChart::class)
            ->assertStatus(200);
    }

    public function test_widget_shows_all_completed_day(): void
    {
        $user = User::factory()->create();
        $habit = Habit::factory()->create([
            'user_id' => $user->id,
            'target_count' => 2,
        ]);

        Todo::factory()->create([
            'user_id' => $user->id,
            'habit_id' => $habit->id,
            'due_date' => now()->subDay(),
            'target_count' => 2,
            'completed_count' => 2,
            'status' => 'completed',
        ]);

        $this->actingAs($user);

        Livewire::test(ContributionsChart::class)
            ->assertSee('contrib-completed');
    }

    public function test_widget_shows_partial_day_with_multiple_habits(): void
    {
        $user = User::factory()->create();
        $habit1 = Habit::factory()->create(['user_id' => $user->id, 'target_count' => 1]);
        $habit2 = Habit::factory()->create(['user_id' => $user->id, 'target_count' => 1]);

        Todo::factory()->create([
            'user_id' => $user->id,
            'habit_id' => $habit1->id,
            'due_date' => now()->subDay(),
            'target_count' => 1,
            'completed_count' => 1,
            'status' => 'completed',
        ]);

        Todo::factory()->create([
            'user_id' => $user->id,
            'habit_id' => $habit2->id,
            'due_date' => now()->subDay(),
            'target_count' => 1,
            'completed_count' => 0,
            'status' => 'pending',
        ]);

        $this->actingAs($user);

        Livewire::test(ContributionsChart::class)
            ->assertSee('contrib-partial');
    }

    public function test_widget_shows_pending_day(): void
    {
        $user = User::factory()->create();
        $habit = Habit::factory()->create([
            'user_id' => $user->id,
            'target_count' => 1,
        ]);

        Todo::factory()->create([
            'user_id' => $user->id,
            'habit_id' => $habit->id,
            'due_date' => now()->subDay(),
            'target_count' => 1,
            'completed_count' => 0,
            'status' => 'pending',
        ]);

        $this->actingAs($user);

        Livewire::test(ContributionsChart::class)
            ->assertSee('contrib-pending');
    }

    public function test_widget_shows_skipped_todo(): void
    {
        $user = User::factory()->create();
        $habit = Habit::factory()->create([
            'user_id' => $user->id,
        ]);

        Todo::factory()->create([
            'user_id' => $user->id,
            'habit_id' => $habit->id,
            'due_date' => now()->subDay(),
            'target_count' => 1,
            'completed_count' => 0,
            'status' => 'skipped',
        ]);

        $this->actingAs($user);

        Livewire::test(ContributionsChart::class)
            ->assertSee('contrib-skipped');
    }

    public function test_weeks_contain_7_days_each(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test(ContributionsChart::class)
            ->assertSet('weeks', function (array $weeks): bool {
                if (empty($weeks)) {
                    return false;
                }

                foreach ($weeks as $week) {
                    if (count($week) !== 7) {
                        return false;
                    }
                }

                return true;
            });
    }

    public function test_widget_only_shows_own_users_data(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $habit1 = Habit::factory()->create(['user_id' => $user1->id]);
        $habit2 = Habit::factory()->create(['user_id' => $user2->id]);

        Todo::factory()->create([
            'user_id' => $user1->id,
            'habit_id' => $habit1->id,
            'due_date' => now()->subDay(),
            'completed_count' => 1,
            'target_count' => 1,
            'status' => 'completed',
        ]);

        Todo::factory()->create([
            'user_id' => $user2->id,
            'habit_id' => $habit2->id,
            'due_date' => now()->subDay(),
            'completed_count' => 0,
            'target_count' => 1,
            'status' => 'pending',
        ]);

        $this->actingAs($user1);

        Livewire::test(ContributionsChart::class)
            ->assertSet('weeks', function (array $weeks): bool {
                $allColors = [];
                foreach ($weeks as $week) {
                    foreach ($week as $day) {
                        $allColors[] = $day['color'];
                    }
                }

                return in_array('contrib-completed', $allColors)
                    && ! in_array('contrib-pending', $allColors);
            });
    }

    public function test_widget_renders_empty_cells_for_no_data(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test(ContributionsChart::class)
            ->assertSee('contrib-none')
            ->assertSee('No task');
    }
}
