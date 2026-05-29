<?php

namespace Database\Seeders;

use App\Models\Sucursal;
use Illuminate\Database\Seeder;

class SucursalSeeder extends Seeder
{
    public function run(): void
    {
        $sucursales = [
            ['nombre' => 'Sucursal Centro', 'direccion' => 'Av. Principal 123, Col. Centro, San Martín',   'telefono' => '+52 (722) 100-0001'],
            ['nombre' => 'Sucursal Norte',  'direccion' => 'Blvd. Norte 456, Col. Las Flores, San Martín', 'telefono' => '+52 (722) 100-0002'],
            ['nombre' => 'Sucursal Sur',    'direccion' => 'Calle Sur 789, Col. Industrial, San Martín',   'telefono' => '+52 (722) 100-0003'],
        ];

        foreach ($sucursales as $data) {
            Sucursal::create($data);
        }
    }
}
