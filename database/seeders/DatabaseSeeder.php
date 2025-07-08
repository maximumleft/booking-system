<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('password'),
            'api_token' => 'john_token_123456789',
        ]);

        User::create([
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
            'password' => bcrypt('password'),
            'api_token' => 'jane_token_987654321',
        ]);

        User::create([
            'name' => 'Bob Wilson',
            'email' => 'bob@example.com',
            'password' => bcrypt('password'),
            'api_token' => 'bob_token_456789123',
        ]);
    }
}
