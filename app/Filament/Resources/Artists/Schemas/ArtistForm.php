<?php

namespace App\Filament\Resources\Artists\Schemas;

use App\Services\AssetUploadService;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ArtistForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Profile')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Textarea::make('bio')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),

                Section::make('Links')
                    ->schema([
                        KeyValue::make('links')
                            ->keyLabel('Platform')
                            ->valueLabel('URL')
                            ->columnSpanFull(),
                    ]),

                Section::make('Hero Image')
                    ->schema([
                        FileUpload::make('hero_image_asset_id')
                            ->image()
                            ->imageEditor()
                            ->visibility('public')
                            ->saveUploadedFileUsing(function ($file) {
                                $asset = app(AssetUploadService::class)->upload($file);

                                return $asset->id;
                            }),
                    ]),
            ]);
    }
}
