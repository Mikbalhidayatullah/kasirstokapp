<?php

namespace App\Http\Controllers\Stock;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\Code128Barcode;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductLabelController extends Controller
{
    public function show(Request $request, Product $product, Code128Barcode $barcode): View
    {
        $product->load('category');

        abort_unless($product->barcode, 404);

        $copies = max(1, min(48, $request->integer('copies', 1)));
        $barcodeSvg = $barcode->renderSvg($product->barcode);

        return view('stock.products.label', compact('product', 'copies', 'barcodeSvg'));
    }
}
