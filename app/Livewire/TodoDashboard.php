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

    public function mount(): void
    {
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

        $service = app(TodoGenerationService::class);
        $service->getTodaysTodos($user);

        $this->todos = $user->todos()
            ->with('habit')
            ->whereDate('due_date', Carbon::today())
            ->orderByRaw("status = 'completed' ASC")
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function render()
    {
        return view('livewire.todo-dashboard');
    }
}
