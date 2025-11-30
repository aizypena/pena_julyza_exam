<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@email.com'],
            [
                'name' => 'John Doe',
                'password' => Hash::make('P@$$w0rd'),
                'role' => 'admin',
                'status' => 'active',
            ]
        );
    }
}