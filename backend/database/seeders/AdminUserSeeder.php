<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // firstOrCreate so redeploys never reset a password changed later.
        User::firstOrCreate(
            ['email' => env('ADMIN_EMAIL', 'admin@taskora.digital')],
            [
                'name' => env('ADMIN_NAME', 'Taskora Admin'),
                'password' => env('ADMIN_PASSWORD', 'password'),
            ],
        );
    }
}
