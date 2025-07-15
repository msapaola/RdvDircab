<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Vérifier si un admin existe déjà
        if (User::where('role', 'admin')->exists()) {
            $this->command->info('Un administrateur existe déjà. Skipping AdminSeeder.');
            return;
        }

        // Créer le compte administrateur initial
        $admin = User::create([
            'name' => 'Administrateur Principal',
            'email' => 'admin@gouvernorat-kinshasa.cd',
            'password' => Hash::make('Admin@2024!'),
            'role' => 'admin',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $this->command->info('Compte administrateur créé avec succès!');
        $this->command->info('Email: admin@gouvernorat-kinshasa.cd');
        $this->command->info('Mot de passe: Admin@2024!');
        $this->command->warn('⚠️  IMPORTANT: Changez le mot de passe après la première connexion!');

        // Créer un compte assistant de test (optionnel)
        if (!User::where('role', 'assistant')->exists()) {
            $assistant = User::create([
                'name' => 'Assistant Test',
                'email' => 'assistant@gouvernorat-kinshasa.cd',
                'password' => Hash::make('Assistant@2024!'),
                'role' => 'assistant',
                'is_active' => true,
                'email_verified_at' => now(),
            ]);

            $this->command->info('Compte assistant de test créé!');
            $this->command->info('Email: assistant@gouvernorat-kinshasa.cd');
            $this->command->info('Mot de passe: Assistant@2024!');
        }
    }
}
