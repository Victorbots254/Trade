<?php

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Support\Facades\Hash;

$admins = [
    [
        'name' => 'Michael Kairithia',
        'email' => 'michaelkairithia@gmail.com',
        'password' => 'Micheal07@!'
    ],
    [
        'name' => 'Viki Gitonga',
        'email' => 'vikigitonga12@gmail.com',
        'password' => 'Victor03480800'
    ]
];

foreach ($admins as $adminData) {
    $user = User::updateOrCreate(
        ['email' => $adminData['email']],
        [
            'name' => $adminData['name'],
            'password' => Hash::make($adminData['password']),
            'is_admin' => true,
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

    echo "Seeded Admin: {$user->email}\n";
}
