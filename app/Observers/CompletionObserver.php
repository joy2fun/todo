<?php

namespace App\Observers;

use App\Models\Completion;
use App\Models\Todo;

class CompletionObserver
{
    public function created(Completion $completion): void
    {
        $this->updateTodoProgress($completion);
    }

    public function deleted(Completion $completion): void
    {
        $this->updateTodoProgress($completion);
    }

    public function restored(Completion $completion): void
    {
        $this->updateTodoProgress($completion);
    }

    protected function updateTodoProgress(Completion $completion): void
    {
        $todo = Todo::withoutTimestamps(fn () => $completion->todo()->first());

        if (! $todo) {
            return;
        }

        $completedCount = $todo->completions()->count();

        $todo->update([
            'completed_count' => $completedCount,
            'status' => $completedCount >= $todo->target_count ? 'completed' : 'pending',
        ]);
    }
}
