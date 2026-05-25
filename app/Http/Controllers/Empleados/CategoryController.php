<?php

namespace App\Http\Controllers\Empleados;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index()
    {
        $categories  = Category::withCount('products')->orderBy('order')->get();
        $iconOptions = Category::iconOptions();

        return view('empleados.categories.index', compact('categories', 'iconOptions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'color'       => 'required|string|size:7',
            'icon'        => 'required|string|in:' . implode(',', array_keys(Category::iconOptions())),
            'order'       => 'integer|min:0',
            'active'      => 'boolean',
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        Category::create($validated);

        return back()->with('success', 'Categoría creada.');
    }

    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'color'       => 'required|string|size:7',
            'icon'        => 'required|string|in:' . implode(',', array_keys(Category::iconOptions())),
            'order'       => 'integer|min:0',
            'active'      => 'boolean',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $category->update($validated);

        return back()->with('success', 'Categoría actualizada.');
    }

    public function destroy(Category $category)
    {
        if ($category->products()->exists()) {
            return back()->with('error', 'No se puede eliminar: la categoría tiene productos asignados.');
        }

        $category->delete();
        return back()->with('success', 'Categoría eliminada.');
    }
}
