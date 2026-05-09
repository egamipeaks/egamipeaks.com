<?php

namespace App\Filament\Resources\Releases\Pages;

use App\Enums\Visibility;
use App\Filament\Resources\Releases\ReleaseResource;
use App\Jobs\SendNewReleaseNotification;
use App\Models\Release;
use App\Models\Subscriber;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;

class EditRelease extends EditRecord
{
    protected static string $resource = ReleaseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('notifySubscribers')
                ->label('Notify subscribers')
                ->icon(Heroicon::PaperAirplane)
                ->color('primary')
                ->requiresConfirmation()
                ->modalHeading('Send new-release email to all verified subscribers?')
                ->modalDescription(fn () => 'This will queue an email to '.Subscriber::verified()->count().' subscribers. This cannot be undone.')
                ->modalSubmitActionLabel('Queue emails')
                ->visible(fn (Release $record): bool => $record->visibility === Visibility::Public)
                ->action(function (Release $record): void {
                    $count = 0;

                    Subscriber::verified()
                        ->lazyById()
                        ->each(function (Subscriber $subscriber) use ($record, &$count): void {
                            SendNewReleaseNotification::dispatch($subscriber, $record);
                            $count++;
                        });

                    Notification::make()
                        ->title("Queued {$count} notification".($count === 1 ? '' : 's'))
                        ->success()
                        ->send();
                }),
            DeleteAction::make(),
        ];
    }
}
