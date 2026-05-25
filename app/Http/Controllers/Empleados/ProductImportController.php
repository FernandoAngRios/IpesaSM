<?php

namespace App\Http\Controllers\Empleados;

use App\Exports\ProductTemplateExport;
use App\Http\Controllers\Controller;
use App\Imports\ProductsImport;
use App\Models\Sucursal;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ProductImportController extends Controller
{
    public function create()
    {
        $sucursales = Sucursal::activo()->orderBy('nombre')->get();
        return view('empleados.products.import', compact('sucursales'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'archivo' => 'required|file|mimes:xlsx,xls,csv|max:5120',
        ]);

        $import = new ProductsImport();
        Excel::import($import, $request->file('archivo'));

        $mensaje = "Se importaron {$import->created} producto(s) correctamente.";

        if (!empty($import->errors)) {
            return redirect()->route('empleados.products.import')
                ->with('import_success', $mensaje)
                ->with('import_errors', $import->errors);
        }

        return redirect()->route('empleados.products.index')
            ->with('success', $mensaje);
    }

    public function template()
    {
        return Excel::download(new ProductTemplateExport(), 'plantilla_productos.xlsx');
    }
}
