<?php

namespace App\Filament\Widgets;

use App\Models\Todo;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Filament\Widgets\Widget;

class ContributionsChart extends Widget
{
    protected static ?int $sort = 0;

    protected int|string|array $columnSpan = 'full';

    protected string $view = 'filament.widgets.contributions-chart';

    public array $weeks = [];

    public int $weekCount = 0;

    public function mount(): void
    {
        $this->weeks = $this->getWeeks();
        $this->weekCount = count($this->weeks);
    }

    public function getWeeks(): array
    {
        $userId = auth()->id();

        $startDate = Carbon::now()->subDays(179)->startOfWeek(Carbon::MONDAY);
        $endDate = Carbon::now()->endOfWeek(Carbon::SUNDAY);

        $todosByDate = Todo::where('user_id', $userId)
            ->where('due_date', '>=', $startDate)
            ->where('due_date', '<=', $endDate)
            ->get()
            ->groupBy(fn (Todo $todo): string => $todo->due_date->format('Y-m-d'));

        $period = CarbonPeriod::create($startDate, $endDate);

        $days = [];
        foreach ($period as $date) {
            $key = $date->format('Y-m-d');
            $dayTodos = $todosByDate[$key] ?? collect();

            $days[] = [
                'date' => $key,
                'color' => $this->getColorForTodos($dayTodos),
                'label' => $this->getLabelForTodos($dayTodos, $date),
            ];
        }

        return array_chunk($days, 7);
    }

    private function getColorForTodos($todos): string
    {
        if ($todos->isEmpty()) {
            return 'contrib-none';
        }

        $totalTarget = $todos->sum('target_count');
        $totalCompleted = $todos->sum('completed_count');

        if ($totalCompleted >= $totalTarget) {
            return 'contrib-completed';
        }

        if ($totalCompleted > 0) {
            return 'contrib-partial';
        }

        if ($todos->contains('status', 'skipped')) {
            return 'contrib-skipped';
        }

        return 'contrib-pending';
    }

    private function getLabelForTodos($todos, Carbon $date): string
    {
        if ($todos->isEmpty()) {
            return $date->format('M j, Y').' - No task';
        }

        $formatted = $date->format('M j, Y');
        $totalTarget = $todos->sum('target_count');
        $totalCompleted = $todos->sum('completed_count');

        if ($totalCompleted >= $totalTarget) {
            return $formatted.' - All habits completed';
        }

        if ($totalCompleted > 0) {
            return $formatted.' - '.$totalCompleted.'/'.$totalTarget.' completed';
        }

        if ($todos->contains('status', 'skipped')) {
            return $formatted.' - Skipped';
        }

        return $formatted.' - Pending';
    }
}
