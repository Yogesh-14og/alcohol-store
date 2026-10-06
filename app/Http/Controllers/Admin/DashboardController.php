<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $thresholds = $request->validate([
            'low_stock_threshold' => ['sometimes', 'integer', 'min:0', 'max:1000000'],
            'high_stock_threshold' => ['sometimes', 'integer', 'min:1', 'max:1000000', 'gt:low_stock_threshold'],
        ]);
        $lowStockThreshold = (int) ($thresholds['low_stock_threshold'] ?? 5);
        $highStockThreshold = (int) ($thresholds['high_stock_threshold'] ?? 50);

        $counts = [
            'products' => Product::count(),
            'categories' => Category::count(),
            'orders' => Order::count(),
            'users' => User::count(),
            'pending_orders' => Order::where('status', 'pending')->count(),
        ];
        $recentOrders = Order::with('user')->latest()->take(8)->get();
        $deliveredOrders = Order::with('items')->where('status', 'delivered')->get();
        $ordersMissingCostData = $deliveredOrders->filter(
            fn (Order $order): bool => $order->gross_profit_cents === null,
        )->count();
        $grossProfitCents = $deliveredOrders
            ->filter(fn (Order $order): bool => $order->gross_profit_cents !== null)
            ->sum(fn (Order $order): int => $order->gross_profit_cents);

        $today = today();
        $yesterday = $today->copy()->subDay();
        $lastSevenDaysStart = $today->copy()->subDays(6)->startOfDay();
        $previousMonthStart = $today->copy()->startOfMonth()->subMonth();
        $reportStart = $lastSevenDaysStart->lessThan($previousMonthStart)
            ? $lastSevenDaysStart
            : $previousMonthStart;

        $reportOrders = Order::with('items')
            ->where('status', 'delivered')
            ->whereNotNull('delivered_at')
            ->whereBetween('delivered_at', [$reportStart, $today->copy()->endOfDay()])
            ->get();

        $periods = [
            'Today' => [$today->copy()->startOfDay(), $today->copy()->endOfDay()],
            'Yesterday' => [$yesterday->copy()->startOfDay(), $yesterday->copy()->endOfDay()],
            'Last 7 days' => [$lastSevenDaysStart, $today->copy()->endOfDay()],
            'This month' => [$today->copy()->startOfMonth(), $today->copy()->endOfDay()],
            'Previous month' => [$previousMonthStart, $today->copy()->startOfMonth()->subSecond()],
        ];
        $salesPeriods = [];

        foreach ($periods as $label => [$start, $end]) {
            $periodOrders = $reportOrders->filter(
                fn (Order $order): bool => $order->delivered_at->greaterThanOrEqualTo($start)
                    && $order->delivered_at->lessThanOrEqualTo($end),
            );
            $salesPeriods[$label] = $this->summarizeOrders($periodOrders);
        }

        $dailySales = collect(range(6, 0))
            ->map(function (int $daysAgo) use ($today, $reportOrders): array {
                $date = $today->copy()->subDays($daysAgo);
                $dailyOrders = $reportOrders->filter(
                    fn (Order $order): bool => $order->delivered_at->isSameDay($date),
                );

                return [
                    'label' => $date->format('D, M j'),
                    ...$this->summarizeOrders($dailyOrders),
                ];
            });

        $products = Product::query()->withAvailableStock()->orderBy('name')->get();
        $lowStockProducts = $products
            ->filter(fn (Product $product): bool => $product->available_stock <= $lowStockThreshold)
            ->values();
        $highStockProducts = $products
            ->filter(fn (Product $product): bool => $product->available_stock >= $highStockThreshold)
            ->values();
        $deliveredOrdersMissingDate = Order::where('status', 'delivered')->whereNull('delivered_at')->count();

        return view('admin.dashboard', compact(
            'counts',
            'recentOrders',
            'grossProfitCents',
            'ordersMissingCostData',
            'salesPeriods',
            'dailySales',
            'lowStockThreshold',
            'highStockThreshold',
            'lowStockProducts',
            'highStockProducts',
            'deliveredOrdersMissingDate',
        ));
    }

    /**
     * @param  Collection<int, Order>  $orders
     * @return array{sales_cents: int, gross_profit_cents: int, orders_missing_cost: int}
     */
    private function summarizeOrders(Collection $orders): array
    {
        return [
            'sales_cents' => $orders->sum(
                fn (Order $order): int => (int) round((float) $order->total_amount * 100),
            ),
            'gross_profit_cents' => $orders->sum(
                fn (Order $order): int => $order->gross_profit_cents ?? 0,
            ),
            'orders_missing_cost' => $orders->filter(
                fn (Order $order): bool => $order->gross_profit_cents === null,
            )->count(),
        ];
    }
}
