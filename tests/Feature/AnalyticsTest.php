<?php

use App\Models\PageView;
use App\Models\Release;
use App\Models\Track;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('records a page view for a public GET', function () {
    Release::factory()->public()->create(['slug' => 'demo']);

    $this->withServerVariables(['HTTP_USER_AGENT' => 'Mozilla/5.0 Test Browser'])
        ->get('/releases/demo')
        ->assertOk();

    expect(PageView::query()->where('event_type', 'page_view')->count())->toBe(1);

    $view = PageView::query()->first();
    expect($view->path)->toBe('releases/demo');
    expect($view->route_name)->toBe('releases.show');
    expect($view->visitor_hash)->toHaveLength(64);
});

it('does not record admin requests', function () {
    $this->actingAs(User::factory()->create())
        ->withServerVariables(['HTTP_USER_AGENT' => 'Mozilla/5.0 Test Browser'])
        ->get('/admin');

    expect(PageView::query()->count())->toBe(0);
});

it('does not record bot requests', function () {
    Release::factory()->public()->create(['slug' => 'bot-demo']);

    $this->withServerVariables(['HTTP_USER_AGENT' => 'Googlebot/2.1'])
        ->get('/releases/bot-demo');

    expect(PageView::query()->count())->toBe(0);
});

it('produces the same visitor hash within a day for the same UA+IP', function () {
    Release::factory()->public()->create(['slug' => 'a']);
    Release::factory()->public()->create(['slug' => 'b']);

    $this->withServerVariables([
        'HTTP_USER_AGENT' => 'Mozilla/5.0 Test Browser',
        'REMOTE_ADDR' => '203.0.113.1',
    ])->get('/releases/a');

    $this->withServerVariables([
        'HTTP_USER_AGENT' => 'Mozilla/5.0 Test Browser',
        'REMOTE_ADDR' => '203.0.113.1',
    ])->get('/releases/b');

    expect(PageView::query()->distinct()->count('visitor_hash'))->toBe(1);
});

it('records track plays via the play endpoint', function () {
    $track = Track::factory()->create();

    $this->withServerVariables(['HTTP_USER_AGENT' => 'Mozilla/5.0 Test Browser'])
        ->postJson(route('tracks.play', $track))
        ->assertNoContent();

    $play = PageView::query()->where('event_type', 'track_play')->first();
    expect($play)->not->toBeNull();
    expect($play->subject_id)->toBe($track->id);
    expect($play->subject_type)->toBe(Track::class);
});

it('ignores referrer when it is the same host', function () {
    Release::factory()->public()->create(['slug' => 'self']);

    $this->withServerVariables([
        'HTTP_USER_AGENT' => 'Mozilla/5.0 Test Browser',
        'HTTP_REFERER' => url('/'),
    ])->get('/releases/self');

    expect(PageView::query()->first()->referer)->toBeNull();
});

it('captures external referrer host', function () {
    Release::factory()->public()->create(['slug' => 'ext']);

    $this->withServerVariables([
        'HTTP_USER_AGENT' => 'Mozilla/5.0 Test Browser',
        'HTTP_REFERER' => 'https://example.com/some-page',
    ])->get('/releases/ext');

    expect(PageView::query()->first()->referer)->toBe('https://example.com/some-page');
});
