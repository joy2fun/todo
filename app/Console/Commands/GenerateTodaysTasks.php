<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\TodoGenerationService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:generate-todays-tasks')]
#[Description('Generate today\'s todo tasks for all active users')]
class GenerateTodaysTasks extends Command
{
    public function handle(TodoGenerationService $service): void
    {
        User::chunk(100, fn ($users) => $users->each(
            fn ($user) => $service->getTodaysTodos($user)
        ));

        $this->info('Today\'s tasks generated successfully.');
    }
}
