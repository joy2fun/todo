<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EndpointResource\Pages\ListEndpoints;
use App\Models\Endpoint;
use Closure;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class EndpointResource extends Resource
{
    protected static ?string $model = Endpoint::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-server-stack';

    protected static ?int $navigationSort = 4;

    protected static ?string $navigationLabel = 'Endpoints';

    protected static ?string $modelLabel = 'endpoint';

    protected static ?string $pluralModelLabel = 'endpoints';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Request')
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        Forms\Components\Select::make('method')
                            ->options(Endpoint::METHODS)
                            ->required()
                            ->default('GET')
                            ->native(false),
                        TextInput::make('path')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('/api/health')
                            ->helperText('Matched exactly against the request path. Leading and trailing slashes are normalized.')
                            ->rules([
                                fn (Get $get, TextInput $component): array => [
                                    function (string $attribute, mixed $value, Closure $fail) use ($get, $component): void {
                                        $query = Endpoint::query()
                                            ->where('path', Endpoint::normalizePath((string) $value))
                                            ->where('method', $get('method'));

                                        if ($record = $component->getRecord()) {
                                            $query->whereKeyNot($record->getKey());
                                        }

                                        if ($query->exists()) {
                                            $fail("An endpoint already exists for {$get('method')} {$value}.");
                                        }
                                    },
                                ],
                            ])
                            ->columnSpanFull(),
                    ]),
                Section::make('Response')
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        TextInput::make('status_code')
                            ->label('Status Code')
                            ->required()
                            ->numeric()
                            ->minValue(100)
                            ->maxValue(599)
                            ->default(200),
                        Forms\Components\Select::make('content_type')
                            ->label('Content Type')
                            ->options(Endpoint::CONTENT_TYPES)
                            ->required()
                            ->default('application/json')
                            ->native(false)
                            ->live(),
                        Forms\Components\Textarea::make('body')
                            ->rows(10)
                            ->columnSpanFull()
                            ->rules([
                                fn (Get $get): array => [
                                    function (string $attribute, mixed $value, Closure $fail) use ($get): void {
                                        if ($get('content_type') !== 'application/json') {
                                            return;
                                        }

                                        if (blank($value)) {
                                            return;
                                        }

                                        json_decode((string) $value);

                                        if (json_last_error() !== JSON_ERROR_NONE) {
                                            $fail('The body must be valid JSON when the content type is application/json.');
                                        }
                                    },
                                ],
                            ])
                            ->helperText('Returned verbatim. JSON is validated when the content type is application/json.'),
                        Forms\Components\KeyValue::make('headers')
                            ->keyLabel('Header')
                            ->valueLabel('Value')
                            ->addActionLabel('Add header')
                            ->reorderable(false)
                            ->columnSpanFull(),
                    ]),
                Section::make()
                    ->columnSpanFull()
                    ->schema([
                        Forms\Components\Textarea::make('note')
                            ->rows(3)
                            ->maxLength(1000)
                            ->columnSpanFull(),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->helperText('Inactive endpoints stop being served and fall through to a 404.')
                            ->default(true),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('method')
                    ->label('Method')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('path')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('note')
                    ->limit(50)
                    ->placeholder('—')
                    ->searchable(),
                Tables\Columns\TextColumn::make('content_type')
                    ->label('Content Type')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('headers')
                    ->label('Headers')
                    ->badge()
                    ->formatStateUsing(fn (?array $state): string => collect($state ?? [])->keys()->join(', '))
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('method')
                    ->options(Endpoint::METHODS),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active'),
            ])
            ->actions([
                Action::make('open')
                    ->label('Open')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->iconButton()
                    ->url(fn (Endpoint $record): string => url($record->path), shouldOpenInNewTab: true)
                    ->visible(fn (Endpoint $record): bool => $record->method === 'GET' && $record->is_active),
                EditAction::make()
                    ->iconButton()
                    ->modalHeading('Edit Endpoint')
                    ->modalWidth('2xl'),
                DeleteAction::make()
                    ->iconButton(),
            ])
            ->selectable(false)
            ->emptyStateHeading('No endpoints')
            ->emptyStateDescription('Create an endpoint to serve a custom response for a path that has no route.');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEndpoints::route('/'),
        ];
    }
}
