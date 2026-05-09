<?php

use App\Jobs\SendNewReleaseNotification;
use App\Mail\ConfirmSubscriptionMail;
use App\Mail\NewReleaseMail;
use App\Models\Artist;
use App\Models\Release;
use App\Models\Subscriber;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('subscribes a new email and sends confirmation mail', function () {
    Mail::fake();

    Livewire::test('subscribe-form')
        ->set('email', 'fan@example.com')
        ->call('submit')
        ->assertHasNoErrors()
        ->assertSet('submitted', true);

    $subscriber = Subscriber::query()->where('email', 'fan@example.com')->first();
    expect($subscriber)->not->toBeNull();
    expect($subscriber->isVerified())->toBeFalse();
    expect($subscriber->verify_token)->not->toBeEmpty();
    expect($subscriber->unsubscribe_token)->not->toBeEmpty();

    Mail::assertQueued(ConfirmSubscriptionMail::class, function (ConfirmSubscriptionMail $mail) {
        return $mail->hasTo('fan@example.com');
    });
});

it('rejects invalid emails', function () {
    Mail::fake();

    Livewire::test('subscribe-form')
        ->set('email', 'not-an-email')
        ->call('submit')
        ->assertHasErrors(['email']);

    Mail::assertNothingQueued();
    expect(Subscriber::query()->count())->toBe(0);
});

it('resends confirmation when an unverified email subscribes again', function () {
    Mail::fake();

    Subscriber::factory()->create(['email' => 'pending@example.com']);

    Livewire::test('subscribe-form')
        ->set('email', 'pending@example.com')
        ->call('submit')
        ->assertSet('submitted', true);

    expect(Subscriber::query()->where('email', 'pending@example.com')->count())->toBe(1);
    Mail::assertQueued(ConfirmSubscriptionMail::class);
});

it('does not send a confirmation when a verified email subscribes again', function () {
    Mail::fake();

    Subscriber::factory()->verified()->create(['email' => 'verified@example.com']);

    Livewire::test('subscribe-form')
        ->set('email', 'verified@example.com')
        ->call('submit')
        ->assertSet('submitted', true);

    Mail::assertNothingQueued();
});

it('does not verify a subscriber on a GET request to the verify route', function () {
    $subscriber = Subscriber::factory()->create();

    $this->get(route('subscribe.verify', ['token' => $subscriber->verify_token]))
        ->assertSuccessful()
        ->assertSee('Confirm subscription');

    expect($subscriber->fresh()->isVerified())->toBeFalse();
});

it('verifies a subscriber on a POST to the verify route', function () {
    $subscriber = Subscriber::factory()->create();

    $this->post(route('subscribe.verify.confirm', ['token' => $subscriber->verify_token]))
        ->assertSuccessful()
        ->assertSee("You're in", false);

    expect($subscriber->fresh()->isVerified())->toBeTrue();
});

it('returns 404 for an unknown verify token', function () {
    $this->get(route('subscribe.verify', ['token' => 'bogus']))->assertNotFound();
});

it('does not delete a subscriber on a GET request to the unsubscribe route', function () {
    $subscriber = Subscriber::factory()->verified()->create(['email' => 'still@example.com']);

    $this->get(route('subscribe.unsubscribe', ['token' => $subscriber->unsubscribe_token]))
        ->assertSuccessful()
        ->assertSee('still@example.com');

    expect(Subscriber::query()->where('email', 'still@example.com')->exists())->toBeTrue();
});

it('removes a subscriber on a POST to the unsubscribe route', function () {
    $subscriber = Subscriber::factory()->verified()->create(['email' => 'bye@example.com']);

    $this->post(route('subscribe.unsubscribe.confirm', ['token' => $subscriber->unsubscribe_token]))
        ->assertSuccessful()
        ->assertSee('bye@example.com');

    expect(Subscriber::query()->where('email', 'bye@example.com')->exists())->toBeFalse();
});

it('only sends new-release mail to verified subscribers when the job runs', function () {
    Mail::fake();

    $artist = Artist::factory()->create();
    $release = Release::factory()->for($artist)->public()->create();

    $verified = Subscriber::factory()->verified()->create();
    $unverified = Subscriber::factory()->create();

    SendNewReleaseNotification::dispatchSync($verified, $release);
    SendNewReleaseNotification::dispatchSync($unverified, $release);

    Mail::assertQueuedCount(1);
    Mail::assertQueued(NewReleaseMail::class, fn (NewReleaseMail $mail) => $mail->hasTo($verified->email));
});

it('queues notification jobs for verified subscribers only', function () {
    Queue::fake();

    $artist = Artist::factory()->create();
    $release = Release::factory()->for($artist)->public()->create();

    Subscriber::factory()->verified()->count(3)->create();
    Subscriber::factory()->count(2)->create();

    Subscriber::verified()->each(function (Subscriber $subscriber) use ($release): void {
        SendNewReleaseNotification::dispatch($subscriber, $release);
    });

    Queue::assertPushed(SendNewReleaseNotification::class, 3);
});
