<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TodoResource\Pages\ListTodos;
use App\Models\Todo;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Carbon;

class TodoResource extends Resource
{
    protected static ?string $model = Todo::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-calendar-days';

    protected static string|null|\UnitEnum $navigationGroup = 'Habit Tracking';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'Today\'s Tasks';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make()
                    ->columnSpanFull()
                    ->columns(1)
                    ->schema([
                        Forms\Components\Select::make('habit_id')
                            ->relationship('habit', 'name')
                            ->required()
                            ->disabled()
                            ->columnSpanFull(),
                        Forms\Components\DatePicker::make('due_date')
                            ->required()
                            ->disabled()
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('target_count')
                            ->required()
                            ->numeric()
                            ->disabled()
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('completed_count')
                            ->required()
                            ->numeric()
                            ->disabled()
                            ->columnSpanFull(),
                        Forms\Components\Select::make('status')
                            ->options([
                                'pending' => 'Pending',
                                'completed' => 'Completed',
                                'skipped' => 'Skipped',
                            ])
                            ->required()
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('habit.name')
                    ->label('Habit')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('due_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('completed_count')
                    ->label('Progress')
                    ->formatStateUsing(fn ($state, Todo $record): string => "{$state} / {$record->target_count}")
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'completed' => 'success',
                        'skipped' => 'danger',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('due_date', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'completed' => 'Completed',
                        'skipped' => 'Skipped',
                    ]),
            ])
            ->actions([
                Action::make('logCompletion')
                    ->label('Log Completion')
                    ->icon('heroicon-o-plus-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Log Completion')
                    ->modalDescription('Record one completion for this task.')
                    ->action(function (Todo $record) {
                        $record->completions()->create([
                            'user_id' => auth()->id(),
                            'completed_at' => now(),
                        ]);
                    })
                    ->visible(fn (Todo $record): bool => $record->status === 'pending'),
                EditAction::make()
                    ->modalWidth('2xl'),
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
            'index' => ListTodos::route('/'),
        ];
    }

    public static function mutateQueryBeforeQuery($query): void
    {
        $query->whereDate('due_date', Carbon::today());
    }
}
