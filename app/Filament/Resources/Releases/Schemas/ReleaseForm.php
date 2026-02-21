<?php

namespace App\Filament\Resources\Releases\Schemas;

use App\Enums\ReleaseType;
use App\Enums\Visibility;
use App\Services\AssetUploadService;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ReleaseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Details')
                    ->schema([
                        Select::make('artist_id')
                            ->relationship('artist', 'name')
                            ->searchable()
                            ->required(),
                        TextInput::make('title')
                            ->required()
                            ->maxLength(255),
                        Select::make('type')
                            ->options(ReleaseType::class)
                            ->required(),
                        Select::make('visibility')
                            ->options(Visibility::class)
                            ->default(Visibility::Draft->value)
                            ->required(),
                        DatePicker::make('release_date'),
                        Textarea::make('description')
                            ->rows(4)
                            ->columnSpanFull(),
                        Textarea::make('credits')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Cover Art')
                    ->schema([
                        FileUpload::make('cover_asset_id')
                            ->image()
                            ->imageEditor()
                            ->visibility('public')
                            ->saveUploadedFileUsing(function ($file) {
                                $asset = app(AssetUploadService::class)->upload($file);

                                return $asset->id;
                            }),
                    ]),

                Section::make('Tags')
                    ->schema([
                        CheckboxList::make('tags')
                            ->relationship('tags', 'name')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
