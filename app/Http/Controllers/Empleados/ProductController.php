<?php

namespace App\Http\Controllers\Empleados;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Sucursal;
use App\Models\SucursalProducto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $allowed   = ['name', 'price', 'coverage', 'active', 'category'];
        $sort      = in_array($request->get('sort'), $allowed) ? $request->get('sort') : 'name';
        $direction = $request->get('direction') === 'desc' ? 'desc' : 'asc';

        $query = Product::with(['category', 'inventario'])
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->select('products.*');

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('products.name', 'like', "%{$search}%")
                  ->orWhere('products.codigo_barras', 'like', "%{$search}%");
            });
        }

        if ($sort === 'category') {
            $query->orderBy('categories.name', $direction);
        } else {
            $query->orderBy("products.{$sort}", $direction);
        }

        $products   = $query->paginate(15)->withQueryString();
        $sucursales = Sucursal::activo()->orderBy('nombre')->get();

        return view('empleados.products.index', compact('products', 'sucursales', 'sort', 'direction'));
    }

    public function create()
    {
        $categories = Category::active()->get();
        $sucursales = auth()->user()->sucursalesPermitidas();
        return view('empleados.products.create', compact('categories', 'sucursales'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateProduct($request);
        $validated['slug']             = Str::slug($validated['name']);
        $validated['unit']             = $validated['unit'] ?? 'litro';
        $validated['available_colors'] = $this->parseColors($request->input('available_colors'));
        $validated['price']        = $validated['price'] ?? 0;
        $validated['stock_litros'] = $validated['stock_litros'] ?? 0;

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('products', 'public');
        }

        $product = Product::create($validated);

        $this->savePresentations($product, $request->input('presentaciones', []));
        $allowedIds = auth()->user()->sucursalesPermitidas()->pluck('id')->all();
        $this->saveAlmacenStock($product, $request->input('almacenes', []), $allowedIds);

        return redirect()->route('empleados.products.index')
            ->with('success', 'Producto creado correctamente.');
    }

    public function show(Product $product)
    {
        $product->load(['category', 'presentations', 'inventario.sucursal']);
        return view('empleados.products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        $categories = Category::active()->get();
        $sucursales = auth()->user()->sucursalesPermitidas();
        return view('empleados.products.edit', compact('product', 'categories', 'sucursales'));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $this->validateProduct($request, $product->id);
        $validated['slug']             = Str::slug($validated['name']);
        $validated['unit']             = $validated['unit'] ?? $product->unit ?? 'litro';
        $validated['available_colors'] = $this->parseColors($request->input('available_colors'));
        $validated['price']        = $validated['price'] ?? $product->price ?? 0;
        $validated['stock_litros'] = $validated['stock_litros'] ?? $product->stock_litros ?? 0;

        if (!auth()->user()->canEditPrices()) {
            unset($validated['costo_compra'], $validated['unidad_compra'], $validated['porcentaje_ganancia'], $validated['price']);
        }

        if ($request->hasFile('image')) {
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $validated['image'] = $request->file('image')->store('products', 'public');
        } elseif ($request->boolean('remove_image') && $product->image) {
            Storage::disk('public')->delete($product->image);
            $validated['image'] = null;
        }

        $product->update($validated);

        if (auth()->user()->canEditPrices()) {
            $product->presentations()->delete();
            $this->savePresentations($product, $request->input('presentaciones', []));
        }

        $allowedIds = auth()->user()->sucursalesPermitidas()->pluck('id')->all();
        $this->saveAlmacenStock($product, $request->input('almacenes', []), $allowedIds);

        return redirect()->route('empleados.products.index')
            ->with('success', 'Producto actualizado correctamente.');
    }

    public function destroy(Product $product)
    {
        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }
        $product->delete();
        return back()->with('success', 'Producto eliminado.');
    }

    public function destroyImage(Product $product)
    {
        if ($product->image) {
            Storage::disk('public')->delete($product->image);
            $product->update(['image' => null]);
        }
        return back()->with('success', 'Imagen eliminada.');
    }

    private function saveAlmacenStock(Product $product, array $almacenes, array $allowedIds): void
    {
        foreach ($almacenes as $sucursalId => $stock) {
            if (!in_array((int) $sucursalId, $allowedIds, true)) continue;
            if ($stock !== null && $stock !== '') {
                SucursalProducto::updateOrCreate(
                    ['sucursal_id' => $sucursalId, 'product_id' => $product->id],
                    ['stock_litros' => max(0, (float) $stock)]
                );
            }
        }
    }

    private function savePresentations(Product $product, array $presentaciones): void
    {
        foreach ($presentaciones as $p) {
            if (!empty($p['nombre']) && isset($p['litros']) && $p['litros'] > 0 && isset($p['precio'])) {
                $product->presentations()->create([
                    'nombre' => trim($p['nombre']),
                    'litros' => $p['litros'],
                    'precio' => $p['precio'],
                ]);
            }
        }
    }

    private function validateProduct(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'category_id'         => 'required|exists:categories,id',
            'name'                => 'required|string|max:150',
            'codigo_barras'       => 'nullable|string|max:100|unique:products,codigo_barras'.($ignoreId ? ','.$ignoreId : ''),
            'short_description'   => 'nullable|string|max:300',
            'description'         => 'nullable|string',
            'costo_compra'        => 'nullable|numeric|min:0',
            'unidad_compra'       => 'nullable|string|max:30',
            'porcentaje_ganancia' => 'nullable|numeric|min:0|max:9999',
            'price'               => 'nullable|numeric|min:0',
            'coverage'            => 'nullable|numeric|min:0',
            'unit'                => 'nullable|string|max:20',
            'stock_litros'        => 'nullable|numeric|min:0',
            'featured'            => 'boolean',
            'active'              => 'boolean',
            'image'               => 'nullable|image|mimes:jpeg,jpg,png,webp|max:3072',
        ]);
    }

    private function parseColors(?string $input): array
    {
        if (empty($input)) {
            return [];
        }
        return array_filter(array_map('trim', explode(',', $input)));
    }
}
