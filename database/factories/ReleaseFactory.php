<?php

namespace Database\Factories;

use App\Enums\ReleaseType;
use App\Enums\Visibility;
use App\Models\Artist;
use App\Models\Release;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Release>
 */
class ReleaseFactory extends Factory
{
    public function definition(): array
    {
        return [
            'artist_id' => Artist::factory(),
            'type' => fake()->randomElement(ReleaseType::cases()),
            'title' => fake()->sentence(3),
            'release_date' => fake()->dateTimeBetween('-5 years', 'now')->format('Y-m-d'),
            'description' => fake()->paragraphs(2, true),
            'credits' => fake()->sentence(),
            'visibility' => Visibility::Draft,
        ];
    }

    public function public(): static
    {
        return $this->state(['visibility' => Visibility::Public]);
    }

    public function unlisted(): static
    {
        return $this->state(['visibility' => Visibility::Unlisted]);
    }

    public function album(): static
    {
        return $this->state(['type' => ReleaseType::Album]);
    }

    public function ep(): static
    {
        return $this->state(['type' => ReleaseType::EP]);
    }

    public function single(): static
    {
        return $this->state(['type' => ReleaseType::Single]);
    }
}
