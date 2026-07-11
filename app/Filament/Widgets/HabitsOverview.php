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
                    ->state(fn (Habit $record): string => $this->calculateStreak($record->id).' days'),
            ])
            ->striped()
            ->paginated(false)
            ->defaultSort('id', 'desc');
    }

    private function calculateStreak(int $habitId): int
    {
        $dates = Todo::where('habit_id', $habitId)
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

        $first = $dates->first();
        $today = Carbon::today();

        if ($first->lt($today->copy()->subDay())) {
            return 0;
        }

        $streak = 1;
        $current = $first;

        foreach ($dates->skip(1) as $date) {
            if ($current->diffInDays($date) === 1) {
                $streak++;
                $current = $date;
            } else {
                break;
            }
        }

        return $streak;
    }
}
