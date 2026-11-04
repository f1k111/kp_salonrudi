<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Membuat atau memperbarui akun Manager Salon
        User::updateOrCreate(
            ['email' => 'admin@salonrudi.com'],
            [
                'name' => 'Manager Salon',
                'password' => Hash::make('password123'),
                'role' => 'admin',
            ]
        );

        // Membuat atau memperbarui akun Kasir Salon
        User::updateOrCreate(
            ['email' => 'kasir@salonrudi.com'],
            [
                'name' => 'Kasir Salon',
                'password' => Hash::make('password123'),
                'role' => 'kasir',
            ]
        );
    }
}
