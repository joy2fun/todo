<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CompletionResource\Pages\ListCompletions;
use App\Models\Completion;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CompletionResource extends Resource
{
    protected static ?string $model = Completion::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-check-circle';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make()
                    ->columnSpanFull()
                    ->columns(1)
                    ->schema([
                        Forms\Components\Select::make('todo_id')
                            ->relationship('todo', 'id', fn ($query) => $query->with('habit'))
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->habit->name.' — '.$record->due_date)
                            ->required()
                            ->disabled()
                            ->columnSpanFull(),
                        Forms\Components\DateTimePicker::make('completed_at')
                            ->required()
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('note')
                            ->rows(3)
                            ->maxLength(1000)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('todo.habit.name')
                    ->label('Habit')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('completed_at')
                    ->since()
                    ->sortable(),
                Tables\Columns\TextColumn::make('note')
                    ->limit(50)
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('completed_at', 'desc')
            ->actions([
                EditAction::make()
                    ->iconButton()
                    ->modalWidth('2xl'),
                DeleteAction::make()
                    ->iconButton(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCompletions::route('/'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('user_id', auth()->id());
    }
}
