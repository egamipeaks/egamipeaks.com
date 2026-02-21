<?php

namespace Database\Factories;

use App\Models\Artist;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Artist>
 */
class ArtistFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'bio' => fake()->paragraphs(3, true),
            'links' => [
                'bandcamp' => 'https://example.bandcamp.com',
                'instagram' => 'https://instagram.com/example',
            ],
            'hero_image_asset_id' => null,
        ];
    }
}
