<?php

namespace App\Jobs;

use App\Mail\NewReleaseMail;
use App\Models\Release;
use App\Models\Subscriber;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class SendNewReleaseNotification implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $backoff = 60;

    public function __construct(
        public Subscriber $subscriber,
        public Release $release,
    ) {}

    public function handle(): void
    {
        if (! $this->subscriber->isVerified()) {
            return;
        }

        Mail::to($this->subscriber->email)
            ->send(new NewReleaseMail($this->subscriber, $this->release));
    }
}
