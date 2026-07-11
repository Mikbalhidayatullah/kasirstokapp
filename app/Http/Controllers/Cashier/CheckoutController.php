<?php

namespace App\Http\Controllers\Cashier;

use App\Enums\PaymentMethod;
use App\Enums\StockMovementType;
use App\Http\Controllers\Controller;
use App\Http\Requests\CheckoutRequest;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Member;
use App\Models\PointReward;
use App\Models\Promotion;
use App\Models\StockMovement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function index(): View
    {
        $products = Product::query()
            ->with('category')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $recentSales = Sale::query()
            ->with(['cashier', 'member'])
            ->latest('sold_at')
            ->take(8)
            ->get();

        $members = Member::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $promotions = Promotion::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $pointRewards = PointReward::query()
            ->where('is_active', true)
            ->orderBy('points_cost')
            ->get();

        return view('cashier.index', compact('products', 'recentSales', 'members', 'promotions', 'pointRewards'));
    }

    public function store(CheckoutRequest $request): RedirectResponse
    {
        $items = collect($request->validated('items'))
            ->groupBy('product_id')
            ->map(fn (Collection $rows, $productId) => [
                'product_id' => (int) $productId,
                'quantity' => $rows->sum('quantity'),
            ])
            ->values();

        $products = Product::query()
            ->whereIn('id', $items->pluck('product_id'))
            ->get()
            ->keyBy('id');

        $subtotal = 0;
        $totalItems = 0;

        foreach ($items as $item) {
            $product = $products->get($item['product_id']);

            if (! $product || ! $product->is_active) {
                throw ValidationException::withMessages([
                    'items' => 'Salah satu produk tidak aktif atau tidak ditemukan.',
                ]);
            }

            if ($product->stock < $item['quantity']) {
                throw ValidationException::withMessages([
                    'items' => "Stok {$product->name} tidak cukup untuk transaksi ini.",
                ]);
            }

            $subtotal += $product->sale_price * $item['quantity'];
            $totalItems += $item['quantity'];
        }

        $discount = (float) $request->input('discount_amount', 0);
        $member = $request->input('member_id')
            ? Member::query()->lockForUpdate()->find($request->input('member_id'))
            : null;
        $promotion = $request->input('promotion_id')
            ? Promotion::query()->where('is_active', true)->find($request->input('promotion_id'))
            : null;
        $pointReward = $request->input('point_reward_id')
            ? PointReward::query()->where('is_active', true)->find($request->input('point_reward_id'))
            : null;
        $promoDiscount = 0;
        $pointDiscount = 0;
        $pointsRedeemed = 0;

        if ($promotion) {
            if ($promotion->member_only && ! $member) {
                throw ValidationException::withMessages([
                    'promotion_id' => 'Promo ini khusus member. Pilih member terlebih dahulu.',
                ]);
            }

            $promoDiscount = $promotion->calculateDiscount(max(0, $subtotal - $discount));
        }

        if ($pointReward) {
            if (! $member) {
                throw ValidationException::withMessages([
                    'point_reward_id' => 'Penukaran poin hanya bisa dipakai jika transaksi memilih member.',
                ]);
            }

            if ($member->points_balance < $pointReward->points_cost) {
                throw ValidationException::withMessages([
                    'point_reward_id' => "Poin {$member->name} belum cukup untuk reward ini.",
                ]);
            }

            $pointsRedeemed = $pointReward->points_cost;
            $pointDiscount = min((float) $pointReward->discount_amount, max(0, $subtotal - $discount - $promoDiscount));
        }

        $tax = (float) $request->input('tax_amount', 0);
        $grandTotal = max(0, $subtotal - $discount - $promoDiscount - $pointDiscount + $tax);
        $pointsEarned = $member ? (int) floor($grandTotal / 1000) : 0;
        $paymentMethod = PaymentMethod::from($request->string('payment_method')->toString());
        $paidAmount = $paymentMethod->requiresExactPayment()
            ? $grandTotal
            : (float) $request->input('paid_amount', 0);
        $changeAmount = $paymentMethod->requiresExactPayment()
            ? 0
            : $paidAmount - $grandTotal;

        if ($paidAmount < $grandTotal) {
            throw ValidationException::withMessages([
                'paid_amount' => 'Jumlah bayar belum mencukupi total belanja.',
            ]);
        }

        $sale = DB::transaction(function () use ($request, $items, $products, $subtotal, $totalItems, $discount, $promoDiscount, $pointDiscount, $tax, $grandTotal, $paidAmount, $changeAmount, $paymentMethod, $member, $promotion, $pointReward, $pointsEarned, $pointsRedeemed): Sale {
            $sale = Sale::query()->create([
                'invoice_number' => 'INV-'.now()->format('YmdHis'),
                'cashier_id' => $request->user()->id,
                'member_id' => $member?->id,
                'promotion_id' => $promotion?->id,
                'point_reward_id' => $pointReward?->id,
                'total_items' => $totalItems,
                'subtotal' => $subtotal,
                'discount_amount' => $discount,
                'promo_discount_amount' => $promoDiscount,
                'point_discount_amount' => $pointDiscount,
                'tax_amount' => $tax,
                'grand_total' => $grandTotal,
                'paid_amount' => $paidAmount,
                'change_amount' => $changeAmount,
                'points_earned' => $pointsEarned,
                'points_redeemed' => $pointsRedeemed,
                'payment_method' => $paymentMethod,
                'sold_at' => now(),
            ]);

            if ($member) {
                $member->update([
                    'points_balance' => $member->points_balance - $pointsRedeemed + $pointsEarned,
                ]);
            }

            foreach ($items as $item) {
                $product = $products->get($item['product_id']);
                $previousStock = $product->stock;
                $currentStock = $product->stock - $item['quantity'];

                SaleItem::query()->create([
                    'sale_id' => $sale->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product_sku' => $product->sku,
                    'price' => $product->sale_price,
                    'quantity' => $item['quantity'],
                    'subtotal' => $product->sale_price * $item['quantity'],
                ]);

                $product->update([
                    'stock' => $currentStock,
                ]);

                StockMovement::query()->create([
                    'product_id' => $product->id,
                    'user_id' => $request->user()->id,
                    'type' => StockMovementType::Sale,
                    'quantity' => $item['quantity'] * -1,
                    'previous_stock' => $previousStock,
                    'current_stock' => $currentStock,
                    'note' => 'Penjualan '.$sale->invoice_number,
                    'occurred_at' => now(),
                ]);
            }

            return $sale;
        });

        return redirect()
            ->route('sales.receipt', $sale)
            ->with('status', 'Transaksi berhasil diproses dan nota siap dicetak.');
    }
}
