<?php

namespace App\Http\Controllers\Cashier;

use App\Enums\PaymentMethod;
use App\Enums\StockMovementType;
use App\Http\Controllers\Controller;
use App\Http\Requests\CheckoutRequest;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
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
            ->with('cashier')
            ->latest('sold_at')
            ->take(8)
            ->get();

        return view('cashier.index', compact('products', 'recentSales'));
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
        $tax = (float) $request->input('tax_amount', 0);
        $grandTotal = max(0, $subtotal - $discount + $tax);
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

        $sale = DB::transaction(function () use ($request, $items, $products, $subtotal, $totalItems, $discount, $tax, $grandTotal, $paidAmount, $changeAmount, $paymentMethod): Sale {
            $sale = Sale::query()->create([
                'invoice_number' => 'INV-'.now()->format('YmdHis'),
                'cashier_id' => $request->user()->id,
                'total_items' => $totalItems,
                'subtotal' => $subtotal,
                'discount_amount' => $discount,
                'tax_amount' => $tax,
                'grand_total' => $grandTotal,
                'paid_amount' => $paidAmount,
                'change_amount' => $changeAmount,
                'payment_method' => $paymentMethod,
                'sold_at' => now(),
            ]);

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
