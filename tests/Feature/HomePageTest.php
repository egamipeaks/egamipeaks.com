<?php

use App\Models\Artist;
use App\Models\Release;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('returns a successful response', function () {
    Artist::factory()->create();

    $this->get(route('home'))->assertSuccessful();
});

it('shows public release titles on the homepage', function () {
    $artist = Artist::factory()->create();
    $public = Release::factory()->for($artist)->public()->create(['title' => 'Public Album']);

    $this->get(route('home'))
        ->assertSuccessful()
        ->assertSee('Public Album');
});

it('does not show draft release titles on the homepage', function () {
    $artist = Artist::factory()->create();
    Release::factory()->for($artist)->draft()->create(['title' => 'Hidden Draft']);

    $this->get(route('home'))
        ->assertSuccessful()
        ->assertDontSee('Hidden Draft');
});
