<?php

namespace App\Http\Controllers\Empleados;

use App\Http\Controllers\Controller;
use App\Models\Caja;
use App\Models\Category;
use App\Models\ContactMessage;
use App\Models\Devolucion;
use App\Models\InternalMessage;
use App\Models\Product;
use App\Models\Venta;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // ── Mensajes ─────────────────────────────────────────────────────
        $unreadInternal = $user->isAdmin()
            ? InternalMessage::forAdminInbox()->whereNull('read_at')->count()
            : InternalMessage::forEmployeeInbox($user->id)->whereNull('read_at')->count();

        $recentUnreadInternal = $user->isAdmin()
            ? InternalMessage::with('sender')->forAdminInbox()->whereNull('read_at')->latest()->take(5)->get()
            : InternalMessage::with('sender')->forEmployeeInbox($user->id)->whereNull('read_at')->latest()->take(5)->get();

        $unreadMessages  = ContactMessage::unread()->count();
        $recentMessages  = ContactMessage::latest()->take(5)->get();

        // ── KPIs de ventas ───────────────────────────────────────────────
        $ventasHoy    = (float) Venta::cerrada()->whereDate('created_at', today())->sum('total');
        $numVentasHoy = Venta::cerrada()->whereDate('created_at', today())->count();
        $ventasMes    = (float) Venta::cerrada()
                            ->whereYear('created_at', now()->year)
                            ->whereMonth('created_at', now()->month)
                            ->sum('total');
        $devHoy       = (float) Devolucion::whereDate('created_at', today())->sum('total_devuelto');

        // ── Caja ─────────────────────────────────────────────────────────
        $cajasAbiertas = Caja::where('estado', 'abierta')->with('sucursal')->get();
        $saldoCajas    = $cajasAbiertas->sum(fn ($c) => $c->saldoActual());

        // ── Gráfica: ventas últimos 7 días ───────────────────────────────
        $dias = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];

        $ventasSemana = collect(range(6, 0))->map(function ($ago) use ($dias) {
            $fecha = now()->subDays($ago);
            return [
                'etiqueta' => $dias[$fecha->dayOfWeek],
                'total'    => (float) Venta::cerrada()->whereDate('created_at', $fecha)->sum('total'),
                'esHoy'    => $ago === 0,
            ];
        });

        $maxVenta     = $ventasSemana->max('total') ?: 1;
        $ventasSemana = $ventasSemana->map(fn ($d) => [
            ...$d,
            'pct' => max(3, (int) round($d['total'] / $maxVenta * 100)),
        ]);

        // ── Ventas recientes ─────────────────────────────────────────────
        $ventasRecientes = Venta::cerrada()->with('sucursal')->latest()->take(8)->get();

        // ── Catálogo ─────────────────────────────────────────────────────
        $stats = [
            'products'        => Product::count(),
            'categories'      => Category::count(),
            'unread_messages' => $unreadMessages,
            'unread_internal' => $unreadInternal,
            'messages'        => ContactMessage::count(),
        ];

        return view('empleados.dashboard', compact(
            'stats',
            'recentMessages',
            'recentUnreadInternal',
            'ventasHoy',
            'numVentasHoy',
            'ventasMes',
            'devHoy',
            'saldoCajas',
            'cajasAbiertas',
            'ventasSemana',
            'ventasRecientes',
        ));
    }
}
