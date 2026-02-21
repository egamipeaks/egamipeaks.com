<?php

namespace App\Filament\Resources\Releases\Tables;

use App\Enums\ReleaseType;
use App\Enums\Visibility;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ReleasesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('artist.name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('type')
                    ->badge()
                    ->sortable(),
                TextColumn::make('visibility')
                    ->badge()
                    ->sortable(),
                TextColumn::make('release_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('tracks_count')
                    ->counts('tracks')
                    ->label('Tracks'),
            ])
            ->filters([
                SelectFilter::make('visibility')
                    ->options(Visibility::class),
                SelectFilter::make('type')
                    ->options(ReleaseType::class),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('release_date', 'desc');
    }
}
