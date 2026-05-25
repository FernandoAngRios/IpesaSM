<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            // Pintura Vinílica
            [
                'category' => 'pintura-vinilica',
                'name' => 'VinylMax Mate Blanco',
                'short_description' => 'Pintura vinílica mate premium para interiores con máxima cobertura.',
                'description' => 'VinylMax Mate es nuestra pintura vinílica de alta calidad, diseñada para interiores que requieren acabado mate elegante. Excelente poder de cubrimiento y lavabilidad. Ideal para salas, recámaras y pasillos.',
                'price' => 420.00,
                'coverage' => 12.0,
                'available_colors' => ['#ffffff', '#f5f5f0', '#e8e8e0', '#d4d4c8'],
                'featured' => true,
            ],
            [
                'category' => 'pintura-vinilica',
                'name' => 'VinylMax Color Interior',
                'short_description' => 'Pintura vinílica en acabado mate, disponible en miles de colores.',
                'description' => 'Formula avanzada que permite colorimetría personalizada. Resistencia a manchas y fácil limpieza. Bajo olor para espacios habitados.',
                'price' => 480.00,
                'coverage' => 11.0,
                'available_colors' => ['#e74c3c', '#3498db', '#2ecc71', '#f39c12', '#9b59b6', '#1abc9c'],
                'featured' => true,
            ],
            // Pintura de Aceite
            [
                'category' => 'pintura-de-aceite',
                'name' => 'AceiteMax Brillante',
                'short_description' => 'Pintura de aceite con acabado brillante de larga duración.',
                'description' => 'Formulación alquídica de alta calidad para superficies que requieren resistencia y brillo. Excelente para puertas, ventanas y marcos metálicos.',
                'price' => 560.00,
                'coverage' => 10.0,
                'available_colors' => ['#ffffff', '#1a3c5e', '#8B4513', '#2d5a27'],
                'featured' => false,
            ],
            // Pintura para Exteriores
            [
                'category' => 'pintura-para-exteriores',
                'name' => 'ExteriMax Elastomérica',
                'short_description' => 'Pintura elastomérica 100% acrílica para exteriores.',
                'description' => 'Máxima protección contra los elementos. Flexible para cubrir grietas finas. Resistencia UV superior que mantiene el color vivo por más tiempo.',
                'price' => 650.00,
                'coverage' => 8.0,
                'available_colors' => ['#ffffff', '#f5f5dc', '#deb887', '#bc8f5f'],
                'featured' => true,
            ],
            [
                'category' => 'pintura-para-exteriores',
                'name' => 'ExteriMax Fachadas',
                'short_description' => 'Pintura acrílica especial para fachadas de alto desempeño.',
                'description' => 'Diseñada para proteger fachadas en climas extremos. Antimoho, antihongo y resistente a la lluvia ácida. Acabado mate duradero.',
                'price' => 590.00,
                'coverage' => 9.0,
                'available_colors' => ['#ffffff', '#f0e6d3', '#c4a882', '#8b6914'],
                'featured' => false,
            ],
            // Esmaltes
            [
                'category' => 'esmaltes',
                'name' => 'EsmalteMax Industrial',
                'short_description' => 'Esmalte alquídico de alta resistencia para metal y madera.',
                'description' => 'Esmalte brillante de alta calidad para proteger y decorar superficies metálicas y de madera. Excelente adhesión y resistencia a la corrosión.',
                'price' => 520.00,
                'coverage' => 10.0,
                'available_colors' => ['#ffffff', '#000000', '#ff0000', '#0000ff', '#ffd700'],
                'featured' => true,
            ],
            // Impermeabilizantes
            [
                'category' => 'impermeabilizantes',
                'name' => 'AquaShield Techos',
                'short_description' => 'Impermeabilizante elastomérico para techos y azoteas.',
                'description' => 'Sistema impermeabilizante de máxima protección. Se adapta a los movimientos de la estructura. Reflectividad solar que reduce la temperatura interior.',
                'price' => 780.00,
                'coverage' => 4.0,
                'available_colors' => ['#ffffff', '#e5e5e5'],
                'featured' => true,
            ],
            // Pinturas Especiales
            [
                'category' => 'pinturas-especiales',
                'name' => 'TexturaMax Decorativa',
                'short_description' => 'Pintura texturizada para acabados decorativos únicos.',
                'description' => 'Crea efectos visuales y táctiles en paredes interiores y exteriores. Cubre imperfecciones y aporta personalidad a cualquier espacio.',
                'price' => 720.00,
                'coverage' => 5.0,
                'available_colors' => ['#ffffff', '#f5f5dc', '#d2b48c'],
                'featured' => false,
            ],
        ];

        foreach ($products as $data) {
            $category = Category::where('slug', $data['category'])->first();
            if (! $category) {
                continue;
            }

            Product::create([
                'category_id' => $category->id,
                'name' => $data['name'],
                'slug' => Str::slug($data['name']),
                'short_description' => $data['short_description'],
                'description' => $data['description'],
                'price' => $data['price'],
                'coverage' => $data['coverage'],
                'available_colors' => $data['available_colors'],
                'unit' => 'litro',
                'featured' => $data['featured'],
                'active' => true,
            ]);
        }
    }
}
