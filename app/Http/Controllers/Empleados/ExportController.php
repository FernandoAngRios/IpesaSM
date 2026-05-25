<?php

namespace App\Http\Controllers\Empleados;

use App\Exports\InventarioExport;
use App\Exports\VentasExport;
use App\Http\Controllers\Controller;
use App\Models\Sucursal;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ExportController extends Controller
{
    public function ventas(Request $request)
    {
        $request->validate([
            'desde'       => 'nullable|date',
            'hasta'       => 'nullable|date|after_or_equal:desde',
            'sucursal_id' => 'nullable|exists:sucursales,id',
        ]);

        $user      = auth()->user();
        $isAdmin   = $user->isAdmin();
        $desde     = $request->desde;
        $hasta     = $request->hasta;
        $sucursalId = $isAdmin ? ($request->sucursal_id ? (int) $request->sucursal_id : null) : null;
        $sucursalIds = $isAdmin ? [] : $user->sucursalesPermitidas()->pluck('id')->all();

        $suffix   = now()->format('Ymd');
        $filename = "ventas_{$suffix}.xlsx";

        return Excel::download(
            new VentasExport($desde, $hasta, $sucursalId, $isAdmin, $sucursalIds),
            $filename
        );
    }

    public function inventario(Request $request)
    {
        $request->validate([
            'sucursal_id' => 'nullable|exists:sucursales,id',
        ]);

        $user        = auth()->user();
        $isAdmin     = $user->isAdmin();
        $sucursalId  = $isAdmin ? ($request->sucursal_id ? (int) $request->sucursal_id : null) : null;
        $sucursalIds = $isAdmin ? [] : $user->sucursalesPermitidas()->pluck('id')->all();

        $suffix   = now()->format('Ymd');
        $filename = "inventario_{$suffix}.xlsx";

        return Excel::download(
            new InventarioExport($sucursalId, $sucursalIds),
            $filename
        );
    }
}
