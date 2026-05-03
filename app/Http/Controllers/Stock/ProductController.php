<?php

namespace App\Http\Controllers\Stock;

use App\Enums\StockMovementType;
use App\Http\Controllers\Controller;
use App\Http\Requests\ProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(): View
    {
        $products = Product::query()
            ->with('category')
            ->latest()
            ->get();

        $categories = Category::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('stock.products.index', compact('products', 'categories'));
    }

    public function store(ProductRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request): void {
            $product = Product::query()->create([
                ...$request->validated(),
                'is_active' => $request->boolean('is_active', true),
            ]);

            if ($product->stock > 0) {
                StockMovement::query()->create([
                    'product_id' => $product->id,
                    'user_id' => $request->user()->id,
                    'type' => StockMovementType::StockIn,
                    'quantity' => $product->stock,
                    'previous_stock' => 0,
                    'current_stock' => $product->stock,
                    'note' => 'Stok awal saat produk dibuat.',
                    'occurred_at' => now(),
                ]);
            }
        });

        return back()->with('status', 'Produk berhasil ditambahkan.');
    }

    public function update(ProductRequest $request, Product $product): RedirectResponse
    {
        $data = $request->validated();
        unset($data['stock']);

        $product->update([
            ...$data,
            'is_active' => $request->boolean('is_active'),
        ]);

        return back()->with('status', 'Produk berhasil diperbarui.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        if ($product->stockMovements()->exists() || $product->saleItems()->exists()) {
            return back()->with('error', 'Produk sudah punya histori. Nonaktifkan saja jika tidak ingin dipakai lagi.');
        }

        $product->delete();

        return back()->with('status', 'Produk berhasil dihapus.');
    }
}
