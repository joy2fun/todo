<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HabitResource\Pages\ListHabits;
use App\Models\Habit;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class HabitResource extends Resource
{
    protected static ?string $model = Habit::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-check-badge';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make()
                    ->columnSpanFull()
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('description')
                            ->rows(3)
                            ->maxLength(1000)
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('target_count')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->default(1),
                        Forms\Components\TextInput::make('unit')
                            ->required()
                            ->maxLength(255)
                            ->default('times'),
                        Forms\Components\ToggleButtons::make('schedule_days')
                            ->label('Schedule Days')
                            ->options([
                                0 => 'Sun',
                                1 => 'Mon',
                                2 => 'Tue',
                                3 => 'Wed',
                                4 => 'Thu',
                                5 => 'Fri',
                                6 => 'Sat',
                            ])
                            ->multiple()
                            ->required()
                            ->default([0, 1, 2, 3, 4, 5, 6])
                            ->columns(['default' => 2, 'sm' => 4, 'md' => 7])
                            ->columnSpanFull(),
                        Forms\Components\Toggle::make('is_active')
                            ->default(true)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('target_count')
                    ->label('Target')
                    ->sortable(),
                Tables\Columns\TextColumn::make('unit')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('schedule_days')
                    ->label('Schedule')
                    ->formatStateUsing(function ($state): string {
                        $dayNames = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
                        if ($state === [0, 1, 2, 3, 4, 5, 6]) {
                            return 'Daily';
                        }

                        return collect($state)->map(fn ($day) => $dayNames[$day] ?? $day)->implode(', ');
                    }),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active'),
            ])
            ->selectable(false)
            ->actions([
                EditAction::make()
                    ->modalHeading('Edit Habit')
                    ->modalWidth('2xl'),
                DeleteAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListHabits::route('/'),
        ];
    }

    public static function mutateQueryBeforeQuery($query): void
    {
        $query->where('user_id', auth()->id());
    }
}
