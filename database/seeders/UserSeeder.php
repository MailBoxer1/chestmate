<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Создаем администратора
        User::create([
            'name' => 'Администратор',
            'email' => 'admin@chestmate.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        // Создаем обычных пользователей
        User::factory(50)->create();
    }
}
