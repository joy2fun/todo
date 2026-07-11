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

        $todos = Todo::where('user_id', $userId)
            ->where('due_date', '>=', $startDate)
            ->where('due_date', '<=', $endDate)
            ->get()
            ->keyBy(fn (Todo $todo): string => $todo->due_date->format('Y-m-d'));

        $period = CarbonPeriod::create($startDate, $endDate);

        $days = [];
        foreach ($period as $date) {
            $key = $date->format('Y-m-d');
            $todo = $todos[$key] ?? null;

            $days[] = [
                'date' => $date->format('Y-m-d'),
                'color' => $this->getColorForTodo($todo),
                'label' => $this->getLabelForTodo($todo, $date),
            ];
        }

        return array_chunk($days, 7);
    }

    private function getColorForTodo(?Todo $todo): string
    {
        if ($todo === null) {
            return 'contrib-none';
        }

        if ($todo->completed_count >= $todo->target_count) {
            return 'contrib-completed';
        }

        if ($todo->completed_count > 0) {
            return 'contrib-partial';
        }

        if ($todo->status === 'skipped') {
            return 'contrib-skipped';
        }

        return 'contrib-pending';
    }

    private function getLabelForTodo(?Todo $todo, Carbon $date): string
    {
        if ($todo === null) {
            return $date->format('M j, Y').' - No task';
        }

        $formatted = $date->format('M j, Y');

        if ($todo->completed_count >= $todo->target_count) {
            return $formatted.' - Completed ('.$todo->completed_count.'/'.$todo->target_count.')';
        }

        if ($todo->status === 'skipped') {
            return $formatted.' - Skipped';
        }

        return $formatted.' - '.$todo->completed_count.'/'.$todo->target_count.' completed';
    }
}
