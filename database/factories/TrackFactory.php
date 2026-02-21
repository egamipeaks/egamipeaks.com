<?php

namespace Database\Factories;

use App\Models\Release;
use App\Models\Track;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Track>
 */
class TrackFactory extends Factory
{
    public function definition(): array
    {
        return [
            'release_id' => Release::factory(),
            'title' => fake()->sentence(fake()->numberBetween(2, 5)),
            'position' => 1,
            'duration_seconds' => fake()->numberBetween(90, 420),
            'lyrics' => fake()->optional(0.6)->paragraphs(4, true),
            'credits' => fake()->optional(0.4)->sentence(),
            'audio_asset_id' => null,
        ];
    }
}
