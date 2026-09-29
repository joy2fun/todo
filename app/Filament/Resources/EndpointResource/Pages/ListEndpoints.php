<?php

namespace App\Filament\Resources\EndpointResource\Pages;

use App\Filament\Resources\EndpointResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListEndpoints extends ListRecords
{
    protected static string $resource = EndpointResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->modalHeading('Create Endpoint')
                ->modalWidth('2xl'),
        ];
    }
}
