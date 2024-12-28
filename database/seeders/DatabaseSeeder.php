<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'phone' => '0949050806',
            'password' => 'password',
            'role' => 'admin'
        ]);
        User::factory()->create([
            'phone' => '0949050805',
            'password' => 'password',
            'role' => 'store_owner'
        ]);
        User::factory()->create([
            'phone' => '0949050804',
            'password' => 'password',
            'role' => 'customer'
        ]);
    }
}
