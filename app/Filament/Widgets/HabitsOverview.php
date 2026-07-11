<?php

namespace App\Filament\Widgets;

use App\Models\Habit;
use App\Models\Todo;
use Carbon\Carbon;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class HabitsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        $userId = auth()->id();

        return $table
            ->query(Habit::where('user_id', $userId))
            ->heading(__('Habits'))
            ->columns([
                Tables\Columns\TextColumn::make('name')->label(''),
                Tables\Columns\TextColumn::make('completed_days')
                    ->label('')
                    ->icon('heroicon-m-check-circle')
                    ->iconColor('success')
                    ->state(fn (Habit $record): int => $record->todos()
                        ->whereColumn('completed_count', '>=', 'target_count')
                        ->count()),
                Tables\Columns\TextColumn::make('current_streak')
                    ->label('')
                    ->icon('heroicon-m-fire')
                    ->iconColor('warning')
                    ->state(fn (Habit $record): string => $this->calculateStreak($record).' days'),
            ])
            ->striped()
            ->paginated(false)
            ->defaultSort('id', 'desc');
    }

    private function calculateStreak(Habit $habit): int
    {
        $scheduleDays = $habit->schedule_days;

        $dates = Todo::where('habit_id', $habit->id)
            ->whereColumn('completed_count', '>=', 'target_count')
            ->where('due_date', '<=', Carbon::today())
            ->orderByDesc('due_date')
            ->pluck('due_date')
            ->map(fn ($date) => Carbon::parse($date)->startOfDay())
            ->unique()
            ->values();

        if ($dates->isEmpty()) {
            return 0;
        }

        $dates = $dates->filter(fn (Carbon $date) => in_array((int) $date->dayOfWeek, $scheduleDays, true))
            ->values();

        if ($dates->isEmpty()) {
            return 0;
        }

        $mostRecentScheduled = $this->getMostRecentScheduledDay($scheduleDays);

        if ($dates->first()->lt($this->getPreviousScheduledDay($mostRecentScheduled, $scheduleDays))) {
            return 0;
        }

        $streak = 1;
        $current = $dates->first();

        foreach ($dates->skip(1) as $date) {
            if ($date->eq($this->getPreviousScheduledDay($current, $scheduleDays))) {
                $streak++;
                $current = $date;
            } else {
                break;
            }
        }

        return $streak;
    }

    private function getMostRecentScheduledDay(array $scheduleDays): Carbon
    {
        $today = Carbon::today();

        if (in_array((int) $today->dayOfWeek, $scheduleDays, true)) {
            return $today;
        }

        return $this->getPreviousScheduledDay($today, $scheduleDays);
    }

    private function getPreviousScheduledDay(Carbon $date, array $scheduleDays): Carbon
    {
        $prev = $date->copy()->subDay();

        for ($i = 0; $i < 7; $i++) {
            if (in_array((int) $prev->dayOfWeek, $scheduleDays, true)) {
                return $prev;
            }
            $prev->subDay();
        }

        return $prev;
    }
}
