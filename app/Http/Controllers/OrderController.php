<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function confirmation(Request $request, string $orderNumber): View
    {
        $order = $request->user()
            ->orders()
            ->with('items')
            ->where('order_number', $orderNumber)
            ->firstOrFail();

        return view('orders.confirmation', compact('order'));
    }
}
