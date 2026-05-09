<?php

use App\Models\Track;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('increments hearts and sets cookie on first heart', function () {
    $track = Track::factory()->create(['hearts_count' => 0]);

    $response = $this->postJson(route('tracks.heart', $track));

    $response->assertOk()
        ->assertJson(['count' => 1, 'hearted' => true]);

    expect($track->fresh()->hearts_count)->toBe(1);
    $response->assertPlainCookie('hearted_tracks', (string) $track->id);
});

it('does not double-increment when track id is in cookie', function () {
    $track = Track::factory()->create(['hearts_count' => 5]);

    $response = $this->withCredentials()->withUnencryptedCookie('hearted_tracks', (string) $track->id)
        ->postJson(route('tracks.heart', $track));

    $response->assertOk()
        ->assertJson(['count' => 5, 'hearted' => true]);

    expect($track->fresh()->hearts_count)->toBe(5);
});

it('appends new track id to existing cookie list', function () {
    $first = Track::factory()->create(['hearts_count' => 1]);
    $second = Track::factory()->create(['hearts_count' => 0]);

    $response = $this->withCredentials()->withUnencryptedCookie('hearted_tracks', (string) $first->id)
        ->postJson(route('tracks.heart', $second));

    $response->assertOk();
    expect($second->fresh()->hearts_count)->toBe(1);
    $response->assertPlainCookie('hearted_tracks', "{$first->id},{$second->id}");
});
