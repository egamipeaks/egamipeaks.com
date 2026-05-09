<?php

use App\Models\Asset;
use App\Models\Release;
use App\Services\AssetUploadService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('spaces');
});

function makeJpegBytes(int $width = 3000, int $height = 3000): string
{
    $image = imagecreatetruecolor($width, $height);
    imagefilledrectangle($image, 0, 0, $width, $height, imagecolorallocate($image, 200, 100, 50));

    ob_start();
    imagejpeg($image, null, 95);
    $bytes = ob_get_clean();
    imagedestroy($image);

    return $bytes;
}

it('optimizes uploaded images to webp under the size cap', function () {
    $jpeg = makeJpegBytes();
    $tmp = tempnam(sys_get_temp_dir(), 'img').'.jpg';
    file_put_contents($tmp, $jpeg);

    $asset = app(AssetUploadService::class)->uploadFromPath($tmp, 'image/jpeg', 'spaces');

    expect($asset->mime)->toBe('image/webp');
    expect(str_ends_with($asset->path, '.webp'))->toBeTrue();
    expect($asset->bytes)->toBeLessThan(strlen($jpeg));

    $stored = Storage::disk('spaces')->get($asset->path);
    $info = getimagesizefromstring($stored);
    expect(max($info[0], $info[1]))->toBeLessThanOrEqual(AssetUploadService::IMAGE_MAX_EDGE);

    @unlink($tmp);
});

it('does not optimize non-image uploads', function () {
    $tmp = tempnam(sys_get_temp_dir(), 'audio').'.mp3';
    file_put_contents($tmp, str_repeat("\0", 1024));

    $asset = app(AssetUploadService::class)->uploadFromPath($tmp, 'audio/mpeg', 'spaces');

    expect($asset->mime)->toBe('audio/mpeg');
    expect(str_ends_with($asset->path, '.mp3'))->toBeTrue();

    @unlink($tmp);
});

it('backfills existing image assets and shrinks them', function () {
    $jpeg = makeJpegBytes(2400, 2400);
    $sha = hash('sha256', $jpeg);
    $path = 'uploads/legacy/big.jpg';
    Storage::disk('spaces')->put($path, $jpeg);

    $asset = Asset::factory()->image()->create([
        'disk' => 'spaces',
        'path' => $path,
        'bytes' => strlen($jpeg),
        'sha256' => $sha,
    ]);

    $release = Release::factory()->create(['cover_asset_id' => $asset->id]);

    $this->artisan('assets:optimize-images')->assertSuccessful();

    $asset->refresh();
    expect($asset->mime)->toBe('image/webp');
    expect($asset->bytes)->toBeLessThan(strlen($jpeg));
    expect(Storage::disk('spaces')->exists($path))->toBeFalse();
    expect(Storage::disk('spaces')->exists($asset->path))->toBeTrue();
    expect($release->fresh()->cover_asset_id)->toBe($asset->id);
});

it('dry-run does not write any changes', function () {
    $jpeg = makeJpegBytes(2000, 2000);
    $sha = hash('sha256', $jpeg);
    $path = 'uploads/legacy/dry.jpg';
    Storage::disk('spaces')->put($path, $jpeg);

    $asset = Asset::factory()->image()->create([
        'disk' => 'spaces',
        'path' => $path,
        'bytes' => strlen($jpeg),
        'sha256' => $sha,
    ]);

    $this->artisan('assets:optimize-images', ['--dry-run' => true])->assertSuccessful();

    $asset->refresh();
    expect($asset->mime)->toBe('image/jpeg');
    expect($asset->path)->toBe($path);
    expect(Storage::disk('spaces')->exists($path))->toBeTrue();
});
