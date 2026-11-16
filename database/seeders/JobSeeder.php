<?php

namespace Database\Seeders;

use App\Models\Job;
use App\Models\Tag;
use Illuminate\Database\Seeder;

class JobSeeder extends Seeder
{
    public function run(): void
    {
        $tags = Tag::factory(5)->create();

        // PT6 Mini Challenge: Create 100 Jobs
        Job::factory(100)->create()->each(function (Job $job) use ($tags) {
            $job->tags()->attach($tags->random(rand(1, 3))->pluck('id'));
        });
    }
}
