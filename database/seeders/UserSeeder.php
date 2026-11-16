<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'test@example.com',
        ]);

        // PT5 Mini Challenge: 10 Users each with 5 Books
        User::factory(10)
            ->has(Book::factory()->count(5))
            ->create();
    }
}
