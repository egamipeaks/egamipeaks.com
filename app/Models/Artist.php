<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Artist extends Model
{
    /** @use HasFactory<\Database\Factories\ArtistFactory> */
    use HasFactory;

    use HasSlug;

    protected $fillable = [
        'name',
        'slug',
        'bio',
        'links',
        'hero_image_asset_id',
    ];

    protected function casts(): array
    {
        return [
            'links' => 'array',
        ];
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsOnUpdate();
    }

    public function heroImage(): BelongsTo
    {
        return $this->belongsTo(Asset::class, 'hero_image_asset_id');
    }

    public function releases(): HasMany
    {
        return $this->hasMany(Release::class);
    }
}
