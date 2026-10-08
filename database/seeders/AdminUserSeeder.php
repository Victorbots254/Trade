<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'name' => 'Michael Kairithia',
                'email' => 'michaelkairithia@gmail.com',
                'password' => 'Micheal07@!',
                'is_admin' => true,
                'is_moderator' => false,
            ],
            [
                'name' => 'Viki Gitonga',
                'email' => 'vikigitonga12@gmail.com',
                'password' => 'Victor03480800.',
                'is_admin' => false,
                'is_moderator' => true,
            ]
        ];

        foreach ($users as $userData) {
            $user = User::updateOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'password' => Hash::make($userData['password']),
                    'is_admin' => $userData['is_admin'] ?? false,
                    'is_moderator' => $userData['is_moderator'] ?? false,
                    'accepted_terms_at' => now(),
                    'accepted_terms_ip' => '127.0.0.1',
                ]
            );

            // Create Live Wallet
            Wallet::firstOrCreate(
                ['user_id' => $user->id, 'currency' => 'USDT', 'is_demo' => false],
                ['available_balance' => 0.00, 'locked_balance' => 0.00]
            );

            // Create Demo Wallet
            Wallet::firstOrCreate(
                ['user_id' => $user->id, 'currency' => 'USDT', 'is_demo' => true],
                ['available_balance' => 10000.00, 'locked_balance' => 0.00]
            );

            $role = $user->is_admin ? 'Admin' : ($user->is_moderator ? 'Moderator' : 'User');
            $this->command->info("Seeded {$role}: {$user->email}");
        }
    }
}
