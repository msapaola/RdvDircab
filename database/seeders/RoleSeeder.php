<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Note: Les rôles sont gérés directement dans la table users
        // avec les colonnes 'role' et 'is_active'
        
        $this->command->info('RoleSeeder: Les rôles sont gérés via les colonnes role et is_active dans la table users.');
        $this->command->info('Rôles disponibles: admin, assistant');
        $this->command->info('Utilisez AdminSeeder pour créer le compte administrateur initial.');
    }
}
