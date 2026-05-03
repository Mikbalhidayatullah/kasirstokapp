<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use Illuminate\View\View;

class SaleController extends Controller
{
    public function index(): View
    {
        $sales = Sale::query()
            ->with(['cashier', 'items'])
            ->latest('sold_at')
            ->paginate(10);

        return view('sales.index', compact('sales'));
    }

    public function receipt(Sale $sale): View
    {
        $sale->load(['cashier', 'items']);

        return view('sales.receipt', compact('sale'));
    }
}
