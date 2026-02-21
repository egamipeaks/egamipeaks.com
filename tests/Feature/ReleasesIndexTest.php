<?php

use App\Models\Artist;
use App\Models\Release;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('lists only public releases', function () {
    $artist = Artist::factory()->create();
    $public = Release::factory()->for($artist)->public()->create(['title' => 'Visible Release']);
    Release::factory()->for($artist)->draft()->create(['title' => 'Hidden Draft']);
    Release::factory()->for($artist)->unlisted()->create(['title' => 'Hidden Unlisted']);

    $this->get(route('releases.index'))
        ->assertSuccessful()
        ->assertSee('Visible Release')
        ->assertDontSee('Hidden Draft')
        ->assertDontSee('Hidden Unlisted');
});

it('paginates at 12 releases', function () {
    $artist = Artist::factory()->create();
    Release::factory()->for($artist)->public()->count(14)->create();

    $response = $this->get(route('releases.index'));

    $response->assertSuccessful();
    expect($response->viewData('releases')->count())->toBe(12);
    expect($response->viewData('releases')->total())->toBe(14);
});
