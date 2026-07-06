<?php

namespace App\Livewire;

use App\Models\Todo;
use App\Services\TodoGenerationService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Livewire\Component;

class TodoDashboard extends Component
{
    /** @var Collection */
    public $todos = [];

    public string $headTitle = '';

    public function mount(): void
    {
        $user = auth()->user();

        if ($user) {
            $this->headTitle = $user->setting('head_title', 'Today\'s Tasks');
            app(TodoGenerationService::class)->getTodaysTodos($user);
        }

        $this->loadTodos();
    }

    public function markCompleted(int $todoId): void
    {
        $todo = Todo::findOrFail($todoId);
        $user = auth()->user();

        if ($todo->user_id !== $user->id) {
            return;
        }

        $todo->completions()->create([
            'user_id' => $user->id,
            'completed_at' => now(),
        ]);

        // Don't reload here — let JS call refreshTodos after fireworks finish
    }

    public function refreshTodos(): void
    {
        $this->loadTodos();
    }

    protected function loadTodos(): void
    {
        $user = auth()->user();

        if (! $user) {
            $this->todos = collect();

            return;
        }

        $this->todos = $user->todos()
            ->with('habit:id,name')
            ->whereDate('due_date', Carbon::today())
            ->orderByRaw("status = 'completed' ASC")
            ->orderBy('created_at', 'desc')
            ->get(['id', 'target_count', 'completed_count', 'status', 'habit_id', 'due_date']);
    }

    public function render()
    {
        return view('livewire.todo-dashboard');
    }
}
