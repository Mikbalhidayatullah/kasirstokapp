<?php

namespace App\Http\Controllers;

use App\Enums\PaymentMethod;
use App\Models\Product;
use App\Models\Sale;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = request()->user();

        $todaySales = Sale::query()
            ->whereDate('sold_at', today())
            ->selectRaw('COUNT(*) as transactions, COALESCE(SUM(grand_total), 0) as revenue')
            ->first();

        $stats = [
            'product_count' => Product::query()->count(),
            'low_stock_count' => Product::query()->whereColumn('stock', '<=', 'minimum_stock')->count(),
            'today_transactions' => (int) ($todaySales->transactions ?? 0),
            'today_revenue' => (float) ($todaySales->revenue ?? 0),
        ];

        $recentSales = Sale::query()
            ->with('cashier')
            ->latest('sold_at')
            ->take(5)
            ->get();

        $recentMovements = StockMovement::query()
            ->with(['product', 'user'])
            ->latest('occurred_at')
            ->take(6)
            ->get();

        $topProducts = Product::query()
            ->leftJoin('sale_items', 'sale_items.product_id', '=', 'products.id')
            ->select('products.name', DB::raw('COALESCE(SUM(sale_items.quantity), 0) as sold_quantity'))
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('sold_quantity')
            ->take(5)
            ->get();

        $paymentTotals = Sale::query()
            ->whereDate('sold_at', today())
            ->select('payment_method', DB::raw('COUNT(*) as transactions'), DB::raw('COALESCE(SUM(grand_total), 0) as amount'))
            ->groupBy('payment_method')
            ->get()
            ->keyBy('payment_method');

        $paymentBreakdown = collect(PaymentMethod::cases())
            ->map(function (PaymentMethod $method) use ($paymentTotals): array {
                $row = $paymentTotals->get($method->value);

                return [
                    'label' => $method->label(),
                    'tone' => $method->badgeTone(),
                    'transactions' => (int) ($row->transactions ?? 0),
                    'amount' => (float) ($row->amount ?? 0),
                ];
            });

        return view('dashboard', compact('user', 'stats', 'recentSales', 'recentMovements', 'topProducts', 'paymentBreakdown'));
    }
}
