<?php

use App\Models\Artist;
use App\Models\Release;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('returns 200 for a public release', function () {
    $release = Release::factory()->for(Artist::factory())->public()->create();

    $this->get(route('releases.show', ['slug' => $release->slug]))
        ->assertSuccessful();
});

it('returns 404 for a draft release without a token', function () {
    $release = Release::factory()->for(Artist::factory())->draft()->create();

    $this->get(route('releases.show', ['slug' => $release->slug]))
        ->assertNotFound();
});

it('returns 404 for a draft release with a wrong token', function () {
    $release = Release::factory()->for(Artist::factory())->draft()->create();

    $this->get(route('releases.show', ['slug' => $release->slug]).'?token=wrong-token')
        ->assertNotFound();
});

it('returns 200 for a draft release with the correct share token', function () {
    $release = Release::factory()->for(Artist::factory())->draft()->create();

    $this->get(route('releases.show', ['slug' => $release->slug]).'?token='.$release->share_token)
        ->assertSuccessful()
        ->assertSee("You're viewing a private preview of this release.", false);
});

it('returns 200 for an unlisted release with the correct share token', function () {
    $release = Release::factory()->for(Artist::factory())->unlisted()->create();

    $this->get(route('releases.show', ['slug' => $release->slug]).'?token='.$release->share_token)
        ->assertSuccessful()
        ->assertSee("You're viewing a private preview of this release.", false);
});
