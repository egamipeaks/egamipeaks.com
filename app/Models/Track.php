<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Track extends Model
{
    /** @use HasFactory<\Database\Factories\TrackFactory> */
    use HasFactory;

    use HasSlug;

    protected $fillable = [
        'release_id',
        'title',
        'slug',
        'position',
        'duration_seconds',
        'lyrics',
        'credits',
        'audio_asset_id',
        'hearts_count',
        'is_highlighted',
    ];

    protected function casts(): array
    {
        return [
            'duration_seconds' => 'integer',
            'hearts_count' => 'integer',
            'is_highlighted' => 'boolean',
        ];
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('title')
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsOnUpdate();
    }

    public function scopeWithUniqueSlugConstraints(Builder $query, Model $model): Builder
    {
        return $query->where('release_id', $model->release_id);
    }

    public function release(): BelongsTo
    {
        return $this->belongsTo(Release::class);
    }

    public function audioAsset(): BelongsTo
    {
        return $this->belongsTo(Asset::class, 'audio_asset_id');
    }

    public function getFormattedDurationAttribute(): ?string
    {
        if (! $this->duration_seconds) {
            return null;
        }

        $minutes = intdiv($this->duration_seconds, 60);
        $seconds = $this->duration_seconds % 60;

        return sprintf('%d:%02d', $minutes, $seconds);
    }
}
