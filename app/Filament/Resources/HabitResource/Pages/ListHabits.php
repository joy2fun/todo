<?php

namespace App\Filament\Resources\HabitResource\Pages;

use App\Filament\Resources\HabitResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListHabits extends ListRecords
{
    protected static string $resource = HabitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->modalHeading('Create Habit')
                ->modalWidth('2xl')
                ->mutateFormDataUsing(fn (array $data): array => [
                    ...$data,
                    'user_id' => auth()->id(),
                ]),
        ];
    }
}
