<?php

namespace App\Filament\Resources\Releases\RelationManagers;

use App\Services\AssetUploadService;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TracksRelationManager extends RelationManager
{
    protected static string $relationship = 'tracks';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->required()
                    ->maxLength(255),
                TextInput::make('position')
                    ->numeric()
                    ->minValue(1)
                    ->default(1)
                    ->required(),
                Textarea::make('lyrics')
                    ->rows(6)
                    ->columnSpanFull(),
                Textarea::make('credits')
                    ->rows(3)
                    ->columnSpanFull(),
                FileUpload::make('audio_asset_id')
                    ->acceptedFileTypes(['audio/mpeg', 'audio/mp3', 'audio/wav', 'audio/flac', 'audio/ogg', 'audio/aac'])
                    ->maxSize(102400)
                    ->visibility('public')
                    ->saveUploadedFileUsing(function ($file) {
                        $asset = app(AssetUploadService::class)->upload($file);

                        return $asset->id;
                    })
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                TextColumn::make('position')
                    ->sortable(),
                TextColumn::make('title')
                    ->searchable(),
                TextColumn::make('formatted_duration')
                    ->label('Duration')
                    ->state(fn ($record): ?string => $record->formatted_duration),
                IconColumn::make('audio_asset_id')
                    ->label('Audio')
                    ->boolean(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('position', 'asc')
            ->reorderable('position');
    }
}
