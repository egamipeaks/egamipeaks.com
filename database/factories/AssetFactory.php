<?php

namespace Database\Factories;

use App\Models\Asset;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Asset>
 */
class AssetFactory extends Factory
{
    public function definition(): array
    {
        $ext = fake()->randomElement(['mp3', 'flac', 'wav']);

        return [
            'disk' => 'spaces',
            'path' => 'uploads/'.fake()->uuid().'.'.$ext,
            'mime' => match ($ext) {
                'mp3' => 'audio/mpeg',
                'flac' => 'audio/flac',
                'wav' => 'audio/wav',
            },
            'bytes' => fake()->numberBetween(1_000_000, 50_000_000),
            'sha256' => fake()->sha256(),
            'metadata' => null,
        ];
    }

    public function image(): static
    {
        return $this->state([
            'path' => 'uploads/'.fake()->uuid().'.jpg',
            'mime' => 'image/jpeg',
            'bytes' => fake()->numberBetween(100_000, 5_000_000),
        ]);
    }
}
