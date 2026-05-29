<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Pintura Vinilica',         'icon' => 'paint_interior', 'description' => 'Ideal para interiores. Lavable, durable y de facil aplicacion.',               'color' => '#3b82f6', 'order' => 1],
            ['name' => 'Pintura para Exteriores',  'icon' => 'paint_exterior', 'description' => 'Resistente a la intemperie, rayos UV y humedad.',                               'color' => '#10b981', 'order' => 2],
            ['name' => 'Pintura de Aceite',         'icon' => 'paint_oil',      'description' => 'Alta resistencia y brillo superior para superficies de alto trafico.',          'color' => '#f59e0b', 'order' => 3],
            ['name' => 'Esmaltes',                  'icon' => 'enamel',         'description' => 'Acabado brillante y duro para metal, madera y concreto.',                      'color' => '#ef4444', 'order' => 4],
            ['name' => 'Impermeabilizantes',        'icon' => 'waterproof',     'description' => 'Proteccion total contra filtraciones de agua en techos y paredes.',            'color' => '#8b5cf6', 'order' => 5],
            ['name' => 'Pinturas Especiales',       'icon' => 'paint_special',  'description' => 'Texturizadas, decorativas, anticorrosivas y de alta temperatura.',             'color' => '#ec4899', 'order' => 6],
            ['name' => 'Tablaroca y Construccion',  'icon' => 'construction',   'description' => 'Laminas, perfiles, masilla y materiales para construccion en seco.',           'color' => '#78716c', 'order' => 7],
            ['name' => 'Herramientas y Accesorios', 'icon' => 'tools',          'description' => 'Brochas, rodillos, charolas, lijas y todo para una aplicacion perfecta.',      'color' => '#0ea5e9', 'order' => 8],
        ];

        foreach ($categories as $data) {
            Category::updateOrCreate(
                ['slug' => Str::slug($data['name'])],
                [...$data, 'slug' => Str::slug($data['name'])]
            );
        }
    }
}
