<?php

use App\Models\Artist;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('returns a successful response', function () {
    Artist::factory()->create();

    $this->get('/')
        ->assertSuccessful();
});
