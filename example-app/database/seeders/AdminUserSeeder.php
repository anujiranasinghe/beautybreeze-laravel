<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@beautybreeze.com'],
            [
                'name' => 'BeautyBreeze Admin',
                'password' => bcrypt('beautybreezeadmin'),
                'is_admin' => true,
            ]
        );
    }
}

