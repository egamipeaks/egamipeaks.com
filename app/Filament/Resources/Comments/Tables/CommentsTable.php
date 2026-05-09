<?php

namespace App\Filament\Resources\Comments\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use LakM\Commenter\Models\Comment;

class CommentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                IconColumn::make('approved')
                    ->boolean()
                    ->label('OK'),
                TextColumn::make('commenter_name')
                    ->label('Commenter')
                    ->state(fn (Comment $record): string => $record->commenter?->name ?? '?')
                    ->searchable(query: function ($query, string $search) {
                        $query->whereHasMorph('commenter', '*', function ($q) use ($search) {
                            $q->where('name', 'like', "%{$search}%");
                        });
                    }),
                TextColumn::make('text')
                    ->html()
                    ->limit(80)
                    ->wrap(),
                TextColumn::make('commentable')
                    ->label('On')
                    ->state(fn (Comment $record): string => $record->commentable?->title ?? '—')
                    ->url(fn (Comment $record): ?string => $record->commentable
                        ? route('releases.show', ['slug' => $record->commentable->slug])
                        : null
                    )
                    ->openUrlInNewTab(),
                TextColumn::make('reply_id')
                    ->label('Type')
                    ->state(fn (Comment $record): string => $record->reply_id ? 'Reply' : 'Comment')
                    ->badge(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->since()
                    ->sortable(),
            ])
            ->filters([
                TernaryFilter::make('approved')
                    ->label('Approval')
                    ->placeholder('All')
                    ->trueLabel('Approved')
                    ->falseLabel('Pending')
                    ->default(false),
            ])
            ->recordActions([
                Action::make('approve')
                    ->icon(\Filament\Support\Icons\Heroicon::Check)
                    ->color('success')
                    ->visible(fn (Comment $record): bool => ! $record->approved)
                    ->requiresConfirmation()
                    ->action(fn (Comment $record) => $record->update(['approved' => true])),
                Action::make('unapprove')
                    ->icon(\Filament\Support\Icons\Heroicon::XMark)
                    ->color('warning')
                    ->visible(fn (Comment $record): bool => $record->approved)
                    ->requiresConfirmation()
                    ->action(fn (Comment $record) => $record->update(['approved' => false])),
                DeleteAction::make()
                    ->authorize(fn (): bool => true),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('approve')
                        ->icon(\Filament\Support\Icons\Heroicon::Check)
                        ->color('success')
                        ->action(fn (Collection $records) => $records->each->update(['approved' => true]))
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('unapprove')
                        ->icon(\Filament\Support\Icons\Heroicon::XMark)
                        ->color('warning')
                        ->action(fn (Collection $records) => $records->each->update(['approved' => false]))
                        ->deselectRecordsAfterCompletion(),
                    DeleteBulkAction::make()
                        ->authorize(fn (): bool => true),
                ]),
            ]);
    }
}
