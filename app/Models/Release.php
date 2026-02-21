<?php

namespace App\Models;

use App\Enums\ReleaseType;
use App\Enums\Visibility;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Release extends Model
{
    /** @use HasFactory<\Database\Factories\ReleaseFactory> */
    use HasFactory;

    use HasSlug;

    protected $fillable = [
        'artist_id',
        'type',
        'title',
        'slug',
        'release_date',
        'description',
        'credits',
        'visibility',
        'cover_asset_id',
        'share_token',
    ];

    protected function casts(): array
    {
        return [
            'type' => ReleaseType::class,
            'visibility' => Visibility::class,
            'release_date' => 'date',
            'share_token' => 'string',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Release $release): void {
            if (! $release->share_token) {
                $release->share_token = (string) Str::uuid();
            }
        });
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('title')
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsOnUpdate();
    }

    public function artist(): BelongsTo
    {
        return $this->belongsTo(Artist::class);
    }

    public function coverAsset(): BelongsTo
    {
        return $this->belongsTo(Asset::class, 'cover_asset_id');
    }

    public function tracks(): HasMany
    {
        return $this->hasMany(Track::class)->orderBy('position');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'release_tag');
    }

    public function scopePublic(Builder $query): void
    {
        $query->where('visibility', Visibility::Public);
    }

    public function scopeVisibleToPublic(Builder $query): void
    {
        $query->whereIn('visibility', [Visibility::Public, Visibility::Unlisted]);
    }

    public function getFormattedReleaseDateAttribute(): ?string
    {
        if (! $this->release_date) {
            return null;
        }

        return $this->release_date->format('F j, Y');
    }
}
