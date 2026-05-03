<?php

namespace App\Http\Controllers\Stock;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryRequest;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        $categories = Category::query()
            ->withCount('products')
            ->latest()
            ->get();

        return view('stock.categories.index', compact('categories'));
    }

    public function store(CategoryRequest $request): RedirectResponse
    {
        Category::query()->create([
            ...$request->validated(),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return back()->with('status', 'Kategori berhasil ditambahkan.');
    }

    public function update(CategoryRequest $request, Category $category): RedirectResponse
    {
        $category->update([
            ...$request->validated(),
            'is_active' => $request->boolean('is_active'),
        ]);

        return back()->with('status', 'Kategori berhasil diperbarui.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        if ($category->products()->exists()) {
            return back()->with('error', 'Kategori masih memiliki produk aktif, jadi belum bisa dihapus.');
        }

        $category->delete();

        return back()->with('status', 'Kategori berhasil dihapus.');
    }
}
