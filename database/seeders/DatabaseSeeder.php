<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database: an admin login + the bootstrap menu.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@genzfoods.pk'],
            ['name' => 'Gen Z Admin', 'password' => Hash::make('password'), 'role' => 'admin'],
        );

        $this->call(MenuSeeder::class);
    }
}
