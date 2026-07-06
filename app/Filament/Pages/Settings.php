<?php

namespace App\Filament\Pages;

use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Schema;

class Settings extends Page
{
    protected static bool $shouldRegisterNavigation = false;

    protected string $view = 'filament.pages.settings';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'icon' => auth()->user()->setting('icon'),
            'head_title' => auth()->user()->setting('head_title'),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Form::make([
                    TextInput::make('head_title')
                        ->label('Page Title')
                        ->placeholder('Today\'s Tasks'),
                    FileUpload::make('icon')
                        ->image()
                        ->disk('public')
                        ->directory('settings-icons')
                        ->visibility('public')
                        ->label('Home Screen Icon'),
                ])
                    ->livewireSubmitHandler('save')
                    ->footer([
                        Actions::make([
                            Action::make('save')
                                ->submit('save')
                                ->keyBindings(['mod+s']),
                        ]),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        foreach ($data as $key => $value) {
            auth()->user()->settings()->updateOrCreate(
                ['key' => $key],
                ['value' => $value],
            );
        }

        Notification::make()
            ->success()
            ->title('Settings saved')
            ->send();
    }
}
