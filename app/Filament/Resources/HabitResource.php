<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HabitResource\Pages\ListHabits;
use App\Models\Habit;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class HabitResource extends Resource
{
    protected static ?string $model = Habit::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-check-badge';

    protected static string|null|\UnitEnum $navigationGroup = 'Habit Tracking';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make()
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Textarea::make('description')
                            ->rows(3)
                            ->maxLength(1000),
                        Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('target_count')
                                    ->required()
                                    ->numeric()
                                    ->minValue(1)
                                    ->default(1),
                                Forms\Components\TextInput::make('unit')
                                    ->required()
                                    ->maxLength(255)
                                    ->default('times'),
                            ]),
                        Forms\Components\CheckboxList::make('schedule_days')
                            ->label('Schedule Days')
                            ->options([
                                0 => 'Sunday',
                                1 => 'Monday',
                                2 => 'Tuesday',
                                3 => 'Wednesday',
                                4 => 'Thursday',
                                5 => 'Friday',
                                6 => 'Saturday',
                            ])
                            ->required()
                            ->columns(7),
                        Forms\Components\Toggle::make('is_active')
                            ->default(true),
                    ]),
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
                    ->sortable(),
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
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active'),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
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
}
