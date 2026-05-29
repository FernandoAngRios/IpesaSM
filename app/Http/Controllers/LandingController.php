<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\ContactMessage;
use App\Models\Product;
use App\Models\Sucursal;
use Illuminate\Http\Request;

class LandingController extends Controller
{
    public function index()
    {
        $categories = Category::active()->get();
        $featuredProducts = Product::active()->featured()->with('category')->get();
        $sucursales = Sucursal::activo()->get();

        return view('landing.index', compact('categories', 'featuredProducts', 'sucursales'));
    }

    public function storeContact(Request $request)
    {
        $nombresValidos = Sucursal::activo()->pluck('nombre');

        $validated = $request->validate([
            'sucursal' => ['required', 'string', \Illuminate\Validation\Rule::in($nombresValidos)],
            'name'     => 'required|string|max:100',
            'email'    => 'required|email|max:100',
            'phone'    => 'nullable|string|max:20',
            'subject'  => 'required|string|max:150',
            'message'  => 'required|string|max:2000',
        ]);

        ContactMessage::create($validated);

        return back()->with('success', '¡Mensaje enviado! Te contactaremos pronto.');
    }
}
