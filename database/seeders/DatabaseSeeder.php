<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::first()->update(['balance' => 1000000000]);

        $admin = Role::create([
            'name' => 'admin'
        ]);

        User::factory(5)
            ->create()
            ->each(fn($user) => $user->assignRole('admin'));
        User::factory(50)
            ->create();

        \App\Models\Telegram\Referral::create([
            'name' => 'Blum',
            'price' => 5000,
            'type' => 'one time',
            'fee' => 500,
        ]);
        \App\Models\Telegram\Referral::create([
            'name' => 'Paws',
            'price' => 4500,
            'type' => 'one time',
            'fee' => 500,
        ]);
        \App\Models\Telegram\Referral::create([
            'name' => 'Notcoin',
            'price' => 6000,
            'type' => 'one time',
            'fee' => 1000,
        ]);
        \App\Models\Telegram\Referral::create([
            'name' => 'Hamster',
            'price' => 5000,
            'type' => 'one time',
            'fee' => 1000,
        ]);
    }
}
