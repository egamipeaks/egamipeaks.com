<?php

namespace App\Mail;

use App\Models\Release;
use App\Models\Subscriber;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewReleaseMail extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public Subscriber $subscriber,
        public Release $release,
    ) {}

    public function envelope(): Envelope
    {
        $artist = $this->release->artist?->name;
        $subject = $artist
            ? "New release: {$artist} – {$this->release->title}"
            : "New release: {$this->release->title}";

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.subscribers.new-release',
            with: [
                'release' => $this->release,
                'releaseUrl' => route('releases.show', ['slug' => $this->release->slug]),
                'unsubscribeUrl' => route('subscribe.unsubscribe', ['token' => $this->subscriber->unsubscribe_token]),
            ],
        );
    }
}
