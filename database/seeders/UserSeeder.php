<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Default accounts. Change the passwords right after the first login.
     *
     * @return void
     */
    public function run()
    {
        User::updateOrCreate(['email' => 'admin@laundry.test'], [
            'name' => 'Administrator',
            'password' => Hash::make('password'),
            'role' => User::ROLE_ADMIN,
            'is_active' => true,
        ]);

        User::updateOrCreate(['email' => 'kasir@laundry.test'], [
            'name' => 'Kasir Utama',
            'password' => Hash::make('password'),
            'role' => User::ROLE_KASIR,
            'is_active' => true,
        ]);
    }
}
