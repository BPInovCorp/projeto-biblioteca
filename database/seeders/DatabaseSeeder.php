<?php

namespace Database\Seeders;

use App\Models\User;
use Database\Seeders\BibliotecaSeeder;
use Illuminate\Database\Seeder;
use RuntimeException;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $adminEmail = env('ADMIN_EMAIL');
        $adminPassword = env('ADMIN_PASSWORD');

        if (!$adminEmail || !$adminPassword) {
            throw new RuntimeException('As credenciais do administrador nao estao definidas no .env');
        }

        User::updateOrCreate(
            ['email' => $adminEmail],
            [
                'name' => env('ADMIN_NAME', 'Bruno Pinto'),
                'password' => $adminPassword,
                'role' => 'admin',
            ]
        );

        $this->call(BibliotecaSeeder::class);
    }
}