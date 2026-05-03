<?php

namespace App\Http\Controllers\Stock;

use App\Enums\StockMovementType;
use App\Http\Controllers\Controller;
use App\Http\Requests\StockAdjustmentRequest;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class StockMovementController extends Controller
{
    public function index(): View
    {
        $movements = StockMovement::query()
            ->with(['product', 'user'])
            ->latest('occurred_at')
            ->paginate(12);

        $products = Product::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('stock.movements.index', compact('movements', 'products'));
    }

    public function store(StockAdjustmentRequest $request): RedirectResponse
    {
        $product = Product::query()->findOrFail($request->integer('product_id'));
        $type = StockMovementType::from($request->string('type')->toString());
        $quantity = $request->integer('quantity');

        if ($type === StockMovementType::StockIn && $quantity < 1) {
            throw ValidationException::withMessages([
                'quantity' => 'Stok masuk harus bernilai positif.',
            ]);
        }

        $delta = $type === StockMovementType::StockIn ? abs($quantity) : $quantity;
        $newStock = $product->stock + $delta;

        if ($newStock < 0) {
            throw ValidationException::withMessages([
                'quantity' => 'Penyesuaian membuat stok menjadi minus. Periksa jumlahnya lagi.',
            ]);
        }

        DB::transaction(function () use ($request, $product, $type, $delta, $newStock): void {
            $previousStock = $product->stock;

            $product->update([
                'stock' => $newStock,
            ]);

            StockMovement::query()->create([
                'product_id' => $product->id,
                'user_id' => $request->user()->id,
                'type' => $type,
                'quantity' => $delta,
                'previous_stock' => $previousStock,
                'current_stock' => $newStock,
                'note' => $request->input('note'),
                'occurred_at' => now(),
            ]);
        });

        return back()->with('status', 'Mutasi stok berhasil disimpan.');
    }
}
