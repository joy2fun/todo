<?php

namespace App\Filament\Widgets;

use App\Models\Todo;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class HomeLink extends StatsOverviewWidget
{
    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        return [
            Stat::make('Pending Tasks', Todo::where('user_id', auth()->id())->where('status', 'pending')->whereDate('due_date', Carbon::today())->count())
                ->url(route('todos'))
                ->icon('heroicon-o-calendar-days'),
        ];
    }
}
