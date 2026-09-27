<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\CustomerProfile;
use App\Enums\UserRole;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $password = Hash::make('password123'); // Mot de passe par défaut pour les tests

        // 1. Création de l'Administrateur
        User::updateOrCreate(
            ['email' => 'admin@motelbethuli.com'],
            [
                'id' => Str::uuid(),
                'nom' => 'System',
                'prenom' => 'Admin',
                'password' => $password,
                'role' => UserRole::ADMIN,
                'actif' => true,
            ]
        );

        User::updateOrCreate(
            ['email' => 'gilleskorusaki@gmail.com'],
            [
                'id' => Str::uuid(),
                'nom' => 'Gilles',
                'prenom' => 'Korusaki',
                'password' => $password,
                'role' => UserRole::ADMIN,
                'actif' => true,
            ]
        );

        // 2. Création du Réceptionniste
        User::updateOrCreate(
            ['email' => 'reception@motelbethuli.com'],
            [
                'id' => Str::uuid(),
                'nom' => 'Accueil',
                'prenom' => 'Réception',
                'password' => $password,
                'role' => UserRole::RECEPTIONIST,
                'actif' => true,
            ]
        );

        // 3. Création du Client
        $client = User::updateOrCreate(
            ['email' => 'client@motelbethuli.com'],
            [
                'id' => Str::uuid(),
                'nom' => 'Doe',
                'prenom' => 'John',
                'password' => $password,
                'role' => UserRole::CLIENT,
                'actif' => true,
            ]
        );

        // Créer le profil client associé s'il n'existe pas
        CustomerProfile::firstOrCreate(
            ['user_id' => $client->id]
        );
    }
}
