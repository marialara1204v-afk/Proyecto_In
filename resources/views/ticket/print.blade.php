<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket #{{ str_pad($order->id, 6, '0', STR_PAD_LEFT) }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Courier New', monospace; background: white; padding: 10px; }

        .ticket {
            width: 80mm; margin: 0 auto; background: white;
            padding: 15px; border: 1px solid #ccc;
        }

        .header { text-align: center; border-bottom: 2px dashed #000; padding-bottom: 10px; margin-bottom: 10px; }
        .restaurant-name { font-size: 18px; font-weight: bold; margin-bottom: 2px; }
        .restaurant-sub { font-size: 11px; color: #666; margin-bottom: 5px; }
        .ticket-number { font-size: 12px; color: #666; margin-bottom: 3px; }
        .ticket-date { font-size: 11px; color: #666; margin-bottom: 10px; }

        .mesa-info { background: #f5f5f5; padding: 8px; border-radius: 4px; margin-bottom: 10px; font-size: 13px; }
        .mesa-info strong { display: inline-block; width: 60px; }

        .items-section { border-top: 2px dashed #000; border-bottom: 2px dashed #000; padding: 10px 0; margin-bottom: 10px; }

        .item { display: flex; justify-content: space-between; margin-bottom: 5px; font-size: 12px; }
        .item-name { flex: 1; }
        .item-qty { width: 30px; text-align: center; }
        .item-price { width: 50px; text-align: right; font-weight: bold; }
        .item-note { font-size: 10px; color: #888; padding-left: 10px; margin-bottom: 3px; }

        .totals { padding: 10px 0; font-size: 13px; }
        .total-row { display: flex; justify-content: space-between; margin-bottom: 5px; }
        .total-row.tip { color: #b8860b; }
        .total-row.final { border-top: 1px solid #000; padding-top: 5px; font-weight: bold; font-size: 16px; }

        .payment-method { text-align: center; margin-top: 10px; padding-top: 10px; border-top: 1px solid #ccc; font-size: 12px; }

        .footer { text-align: center; margin-top: 15px; padding-top: 10px; border-top: 2px dashed #000; font-size: 11px; color: #666; }
        .thank-you { text-align: center; font-size: 14px; font-weight: bold; margin-top: 10px; color: #2c5aa0; }

        @media print { body { padding: 0; } .ticket { border: none; width: 100%; padding: 0; } .print-button { display: none; } }
        .print-button { display: block; margin: 20px auto; padding: 10px 20px; background: #2c5aa0; color: white; border: none; border-radius: 5px; font-size: 14px; cursor: pointer; font-family: Arial, sans-serif; }
        .print-button:hover { background: #1e4275; }
    </style>
</head>
<body>
    <button class="print-button" onclick="window.print()">Imprimir Ticket</button>

    <div class="ticket">
        <div class="header">
            <div class="restaurant-name">LA PATRONA</div>
            <div class="restaurant-sub">Restaurante</div>
            <div class="ticket-number">Ticket #{{ str_pad($order->id, 6, '0', STR_PAD_LEFT) }}</div>
            <div class="ticket-date">{{ $order->created_at->format('d/m/Y H:i') }}</div>
        </div>

        <div class="mesa-info">
            <div><strong>Mesa:</strong> {{ $order->table?->number ?? 'N/A' }}</div>
            <div><strong>Mesero:</strong> {{ $order->user->name ?? 'Auto-servicio' }}</div>
            <div><strong>Estado:</strong> {{ ucfirst($order->status) }}</div>
        </div>

        <div class="items-section">
            @foreach ($order->items as $item)
                <div class="item">
                    <div class="item-name">{{ $item->quantity }}x {{ $item->product->name }}</div>
                    <div class="item-price">${{ number_format($item->subtotal, 2) }}</div>
                </div>
                @if ($item->notes)
                    <div class="item-note">{{ $item->notes }}</div>
                @endif
            @endforeach
        </div>

        <div class="totals">
            <div class="total-row">
                <span>Subtotal:</span>
                <span>${{ number_format($order->total, 2) }}</span>
            </div>
            @if ($order->tip > 0)
                <div class="total-row tip">
                    <span>Propina:</span>
                    <span>${{ number_format($order->tip, 2) }}</span>
                </div>
            @endif
            @if ($payment && $payment->tip > 0 && $order->tip == 0)
                <div class="total-row tip">
                    <span>Propina:</span>
                    <span>${{ number_format($payment->tip, 2) }}</span>
                </div>
            @endif
            <div class="total-row final">
                <span>TOTAL:</span>
                <span>${{ number_format($order->total + ($order->tip ?? 0) + ($payment->tip ?? 0), 2) }}</span>
            </div>
        </div>

        @if ($payment)
            <div class="payment-method">
                <strong>Metodo de Pago:</strong> @switch($payment->payment_method)
                    @case('efectivo') Efectivo @break
                    @case('tarjeta') Tarjeta @break
                    @case('transferencia') Transferencia @break
                    @default {{ ucfirst($payment->payment_method) }}
                @endswitch<br>
                <small>Pagado: {{ $payment->created_at->format('d/m/Y H:i') }}</small>
            </div>
        @endif

        <div class="footer">
            <p>Gracias por su preferencia</p>
            <p style="margin-top: 5px;">Esperamos verlo pronto</p>
        </div>

        <div class="thank-you">LA PATRONA</div>
    </div>
</body>
</html>
