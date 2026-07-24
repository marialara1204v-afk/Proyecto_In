<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderPayment;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    public function print($orderId)
    {
        $order = Order::with('items.product', 'user', 'table')->findOrFail($orderId);

        $user = auth()->user();
        if ($user->role !== 'admin' && $order->user_id !== $user->id) {
            abort(403, 'No tienes permiso para ver este ticket.');
        }

        $payment = $order->payments()->latest()->first();

        return view('ticket.print', compact('order', 'payment'));
    }
}
