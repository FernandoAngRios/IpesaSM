<?php

namespace App\Http\Controllers\Empleados;

use App\Http\Controllers\Controller;
use App\Models\Sucursal;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('sucursales')->orderBy('name')->get();
        return view('empleados.usuarios.index', compact('users'));
    }

    public function create()
    {
        $sucursales = Sucursal::activo()->orderBy('nombre')->get();
        return view('empleados.usuarios.create', compact('sucursales'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'            => 'required|string|max:150',
            'password'        => 'required|string|min:8|confirmed',
            'role'            => 'required|in:admin,employee',
            'can_edit_prices' => 'boolean',
            'active'          => 'boolean',
            'sucursales'      => 'nullable|array',
            'sucursales.*'    => 'exists:sucursales,id',
        ]);

        $baseSlug = Str::slug($data['name'], '.');
        $email = $baseSlug . '@interno.ipesa';
        $suffix = 1;
        while (User::where('email', $email)->exists()) {
            $email = $baseSlug . $suffix . '@interno.ipesa';
            $suffix++;
        }

        $user = User::create([
            'name'            => $data['name'],
            'email'           => $email,
            'password'        => $data['password'],
            'role'            => $data['role'],
            'can_edit_prices' => $request->boolean('can_edit_prices'),
            'active'          => $request->boolean('active', true),
        ]);

        $user->sucursales()->sync($data['sucursales'] ?? []);

        return redirect()->route('empleados.usuarios.index')
            ->with('success', 'Usuario creado correctamente.');
    }

    public function edit(User $user)
    {
        $sucursales = Sucursal::activo()->orderBy('nombre')->get();
        $user->load('sucursales');
        return view('empleados.usuarios.edit', compact('user', 'sucursales'));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name'            => 'required|string|max:150',
            'password'        => 'nullable|string|min:8|confirmed',
            'role'            => 'required|in:admin,employee',
            'can_edit_prices' => 'boolean',
            'active'          => 'boolean',
            'sucursales'      => 'nullable|array',
            'sucursales.*'    => 'exists:sucursales,id',
        ]);

        $updateData = [
            'name'            => $data['name'],
            'role'            => $data['role'],
            'can_edit_prices' => $request->boolean('can_edit_prices'),
            'active'          => $request->boolean('active'),
        ];

        if (!empty($data['password'])) {
            $updateData['password'] = $data['password'];
        }

        $user->update($updateData);
        $user->sucursales()->sync($data['sucursales'] ?? []);

        return redirect()->route('empleados.usuarios.index')
            ->with('success', 'Usuario actualizado correctamente.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'No puedes eliminarte a ti mismo.');
        }

        $user->delete();
        return back()->with('success', 'Usuario eliminado.');
    }
}
