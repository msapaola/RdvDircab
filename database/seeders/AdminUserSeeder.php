<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user
        User::create([
            'name' => 'Administrateur',
            'email' => 'admin@cabinet-gouverneur.com',
            'password' => Hash::make('admin123'),
            'role' => User::ROLE_ADMIN,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        // Create assistant user (optional)
        User::create([
            'name' => 'Assistant',
            'email' => 'assistant@cabinet-gouverneur.com',
            'password' => Hash::make('assistant123'),
            'role' => User::ROLE_ASSISTANT,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $this->command->info('Users créés avec succès !');
        $this->command->info('Admin: admin@cabinet-gouverneur.com / admin123');
        $this->command->info('Assistant: assistant@cabinet-gouverneur.com / assistant123');
    }
} 