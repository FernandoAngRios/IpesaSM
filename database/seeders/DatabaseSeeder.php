<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        User::create([
            'name' => 'Administrador',
            'email' => 'admin@ipesasm.com',
            'password' => Hash::make('admin1234'),
            'role' => 'admin',
            'active' => true,
        ]);

        User::create([
            'name' => 'Empleado Demo',
            'email' => 'empleado@ipesasm.com',
            'password' => Hash::make('empleado1234'),
            'role' => 'employee',
            'active' => true,
        ]);

        $this->call([
            CategorySeeder::class,
            ProductSeeder::class,
            SucursalSeeder::class,
        ]);
    }
}
