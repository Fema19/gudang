<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Operator saja (karyawan tidak perlu akun login)
        User::updateOrCreate(
            ['email' => 'operator@example.com'], //email bisa di ubah
            [
                'name' => 'Operator Gudang',
                'password' => Hash::make('password123'), // password bisa di ubah
                'role' => 'operator'
            ]
        );
    }
}
