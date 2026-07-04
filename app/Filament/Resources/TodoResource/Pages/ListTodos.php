<?php

namespace App\Filament\Resources\TodoResource\Pages;

use App\Filament\Resources\TodoResource;
use App\Services\TodoGenerationService;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTodos extends ListRecords
{
    protected static string $resource = TodoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('generate')
                ->label('Generate Today\'s Tasks')
                ->icon('heroicon-o-arrow-path')
                ->action(function () {
                    $service = app(TodoGenerationService::class);
                    $service->getTodaysTodos(auth()->user());

                    $this->dispatch('refreshTable');
                })
                ->requiresConfirmation()
                ->modalHeading('Generate Today\'s Tasks')
                ->modalDescription('Create todo records for today based on your active habits.'),
        ];
    }
}
