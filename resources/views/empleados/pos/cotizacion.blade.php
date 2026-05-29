<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cotización — IPESA SM</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Segoe UI', system-ui, sans-serif;
            font-size: 13px;
            color: #111;
            background: #f5f5f5;
        }

        .page {
            background: #fff;
            width: 80mm;
            margin: 24px auto;
            padding: 16px;
        }

        /* Botones de acción (solo pantalla, no se imprimen) */
        .actions {
            display: flex;
            gap: 8px;
            justify-content: center;
            margin-bottom: 16px;
        }
        .btn {
            padding: 8px 20px;
            border-radius: 8px;
            border: none;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
        }
        .btn-print { background: #1d4ed8; color: #fff; }
        .btn-close { background: #e5e7eb; color: #374151; }

        /* Cabecera */
        .header { text-align: center; margin-bottom: 14px; padding-bottom: 10px; border-bottom: 1px dashed #ccc; }
        .header h1 { font-size: 16px; font-weight: 800; letter-spacing: 0.5px; }
        .header .badge {
            display: inline-block;
            margin-top: 4px;
            padding: 2px 10px;
            background: #dbeafe;
            color: #1d4ed8;
            border-radius: 99px;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        .header .meta { font-size: 11px; color: #6b7280; margin-top: 6px; line-height: 1.6; }

        /* Items */
        .items { width: 100%; margin: 12px 0; border-collapse: collapse; }
        .items th {
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            color: #9ca3af;
            letter-spacing: 0.5px;
            padding: 0 0 6px;
            border-bottom: 1px solid #e5e7eb;
        }
        .items th:last-child, .items td:last-child { text-align: right; }
        .items td { padding: 6px 0; border-bottom: 1px solid #f3f4f6; vertical-align: top; }
        .items td .nombre { font-weight: 600; }
        .items td .pres { font-size: 11px; color: #6b7280; }

        /* Totales */
        .totals { margin-top: 10px; padding-top: 10px; border-top: 1px dashed #ccc; }
        .totals .row { display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 4px; }
        .totals .row.total { font-size: 15px; font-weight: 800; margin-top: 6px; }

        /* Nota */
        .nota {
            margin-top: 14px;
            padding-top: 10px;
            border-top: 1px dashed #ccc;
            font-size: 11px;
            color: #6b7280;
            text-align: center;
            line-height: 1.6;
        }

        @media print {
            body { background: #fff; }
            .page { margin: 0; padding: 12px; width: 100%; box-shadow: none; }
            .actions { display: none; }
        }
    </style>
</head>
<body>

<div class="actions">
    <button class="btn btn-print" onclick="window.print()">Imprimir</button>
    <button class="btn btn-close" onclick="window.close()">Cerrar</button>
</div>

<div class="page">

    <div class="header">
        <h1>IPESA SM</h1>
        <span class="badge">Cotización</span>
        <div class="meta">
            {{ $venta->sucursal->nombre }}<br>
            Folio #{{ str_pad($venta->id, 6, '0', STR_PAD_LEFT) }}<br>
            {{ now()->format('d/m/Y H:i') }}<br>
            @if($venta->vendedor)Vendedor: {{ $venta->vendedor }}@endif
        </div>
    </div>

    <table class="items">
        <thead>
            <tr>
                <th style="width:50%">Producto</th>
                <th style="text-align:center">Cant.</th>
                <th style="text-align:right">P.U.</th>
                <th style="text-align:right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($venta->items as $item)
            <tr>
                <td>
                    <div class="nombre">{{ $item->nombre_producto }}</div>
                    @if($item->nombre_presentacion)
                    <div class="pres">{{ $item->nombre_presentacion }}</div>
                    @endif
                </td>
                <td style="text-align:center">{{ rtrim(rtrim(number_format($item->cantidad, 3), '0'), '.') }}</td>
                <td style="text-align:right">${{ number_format($item->precio_unitario, 2) }}</td>
                <td style="text-align:right">${{ number_format($item->subtotal, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totals">
        <div class="row total">
            <span>Total</span>
            <span>${{ number_format($venta->total, 2) }}</span>
        </div>
    </div>

    <div class="nota">
        Esta es una cotización, no un comprobante de pago.<br>
        Precios sujetos a cambio sin previo aviso.
    </div>

</div>

<script>
    window.addEventListener('load', () => window.print());
</script>
</body>
</html>
