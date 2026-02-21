<?php

namespace Database\Seeders;

use App\Models\Artist;
use App\Models\Release;
use App\Models\Tag;
use App\Models\Track;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@egamipeaks.com',
            'password' => bcrypt('password'),
        ]);

        $artist = Artist::factory()->create(['name' => 'egamipeaks']);

        $electronic = Tag::factory()->create(['name' => 'Electronic']);
        $ambient = Tag::factory()->create(['name' => 'Ambient']);
        $experimental = Tag::factory()->create(['name' => 'Experimental']);

        $album = Release::factory()
            ->album()
            ->public()
            ->create([
                'artist_id' => $artist->id,
                'title' => 'First Light',
            ]);

        Track::factory()
            ->count(8)
            ->sequence(fn ($sequence) => ['position' => $sequence->index + 1])
            ->create(['release_id' => $album->id]);

        $album->tags()->attach([$electronic->id, $ambient->id]);

        $ep = Release::factory()
            ->ep()
            ->public()
            ->create([
                'artist_id' => $artist->id,
                'title' => 'Fragments',
            ]);

        Track::factory()
            ->count(4)
            ->sequence(fn ($sequence) => ['position' => $sequence->index + 1])
            ->create(['release_id' => $ep->id]);

        $ep->tags()->attach([$experimental->id]);

        $single = Release::factory()
            ->single()
            ->create([
                'artist_id' => $artist->id,
                'title' => 'Drift',
            ]);

        Track::factory()->create(['release_id' => $single->id, 'position' => 1]);

        $single->tags()->attach([$ambient->id]);
    }
}
