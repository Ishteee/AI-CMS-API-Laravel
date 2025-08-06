<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Admin 1',
            'email' => 'admin1@cmsapi.com',
            'role' => 'admin',
            'password' => Hash::make('admin123'),
        ]);

        User::create([
            'name' => 'Author 1',
            'email' => 'author1@cmsapi.com',
            'role' => 'author',
            'password' => Hash::make('author123'),
        ]);

        User::create([
            'name' => 'Author 2',
            'email' => 'author2@cmsapi.com',
            'role' => 'author',
            'password' => Hash::make('author123'),
        ]);

        User::create([
            'name' => 'Author 3',
            'email' => 'author3@cmsapi.com',
            'role' => 'author',
            'password' => Hash::make('author123'),
        ]);
    }
}