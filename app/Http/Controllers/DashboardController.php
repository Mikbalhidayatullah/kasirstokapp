<?php

namespace App\Http\Controllers;

use App\Enums\PaymentMethod;
use App\Models\Member;
use App\Models\Product;
use App\Models\Sale;
use App\Models\StockMovement;
use Carbon\CarbonPeriod;
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
            'member_count' => Member::query()->where('is_active', true)->count(),
            'total_member_count' => Member::query()->count(),
            'today_transactions' => (int) ($todaySales->transactions ?? 0),
            'today_revenue' => (float) ($todaySales->revenue ?? 0),
        ];

        $stats['healthy_stock_count'] = max(0, $stats['product_count'] - $stats['low_stock_count']);
        $stats['stock_health_percent'] = $this->percentage($stats['healthy_stock_count'], $stats['product_count']);
        $stats['low_stock_percent'] = $this->percentage($stats['low_stock_count'], $stats['product_count']);
        $stats['active_member_percent'] = $this->percentage($stats['member_count'], $stats['total_member_count']);

        $recentSales = Sale::query()
            ->with(['cashier', 'member'])
            ->latest('sold_at')
            ->take(12)
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

        $totalPaymentAmount = max(1, (float) $paymentBreakdown->sum('amount'));
        $paymentBreakdown = $paymentBreakdown
            ->map(fn (array $payment): array => [
                ...$payment,
                'percent' => (int) round(($payment['amount'] / $totalPaymentAmount) * 100),
            ]);

        $salesTrend = $this->salesTrend();

        return view('dashboard', compact('user', 'stats', 'recentSales', 'recentMovements', 'topProducts', 'paymentBreakdown', 'salesTrend'));
    }

    private function percentage(int|float $value, int|float $total): int
    {
        if ($total <= 0) {
            return 0;
        }

        return (int) round(($value / $total) * 100);
    }

    private function salesTrend(): array
    {
        $startDate = now()->subDays(6)->startOfDay();
        $endDate = now()->endOfDay();

        $sales = Sale::query()
            ->whereBetween('sold_at', [$startDate, $endDate])
            ->get(['sold_at', 'grand_total']);

        $grouped = $sales->groupBy(fn (Sale $sale) => $sale->sold_at->toDateString());
        $rows = collect(CarbonPeriod::create($startDate, $endDate))
            ->map(fn ($date): array => [
                'label' => $date->format('d M'),
                'amount' => (float) ($grouped->get($date->toDateString())?->sum('grand_total') ?? 0),
            ]);

        $maxAmount = max(1, (float) $rows->max('amount'));

        return $rows
            ->map(fn (array $row): array => [
                ...$row,
                'height' => max(8, (int) round(($row['amount'] / $maxAmount) * 100)),
            ])
            ->all();
    }
}
