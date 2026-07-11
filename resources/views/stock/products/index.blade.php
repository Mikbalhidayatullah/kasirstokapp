<x-layouts.app
    title="Produk"
    heading="Inventaris Produk"
    description="Kelola liquid, pod, device, coil, kapas, baterai, lokasi simpan, barcode, harga, dan batas minimum stok."
>
    <section class="surface-card p-5">
        <h2 class="section-title">Tambah Produk Baru</h2>
        <p class="section-copy mt-1">Produk baru akan langsung tersedia di modul kasir jika statusnya aktif.</p>

        <form method="POST" action="{{ route('stock.products.store') }}" class="mt-5 grid gap-4 lg:grid-cols-2">
            @csrf

            <div>
                <x-ui.select label="Kategori" name="category_id" required>
                    <option value="">Pilih kategori</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected(old('category_id') == $category->id)>{{ $category->name }}</option>
                    @endforeach
                </x-ui.select>
            </div>

            <div>
                <x-ui.input label="SKU" name="sku" placeholder="Contoh: LQD-BC-30" required />
            </div>

            <div>
                <x-ui.input label="Barcode" name="barcode" placeholder="Scan atau input barcode barang" />
            </div>

            <div>
                <x-ui.input label="Nama produk" name="name" placeholder="Contoh: Liquid Blueberry Cream 30ml" required />
            </div>

            <div>
                <x-ui.input label="Satuan" name="unit" placeholder="pcs / botol / pack / coil" required />
            </div>

            <div>
                <x-ui.input label="Lokasi penyimpanan" name="storage_location" placeholder="Etalase A2, Rak Liquid Freebase, Laci Coil" />
            </div>

            <div>
                <x-ui.input label="Harga modal" name="cost_price" type="number" min="0" step="0.01" required />
            </div>

            <div>
                <x-ui.input label="Harga jual" name="sale_price" type="number" min="0" step="0.01" required />
            </div>

            <div>
                <x-ui.input label="Stok awal" name="stock" type="number" min="0" required />
            </div>

            <div>
                <x-ui.input label="Batas minimum stok" name="minimum_stock" type="number" min="0" required />
            </div>

            <div class="lg:col-span-2">
                <x-ui.textarea label="Deskripsi" name="description" rows="3" placeholder="Catatan rasa, varian nikotin, kompatibilitas device, atau supplier"></x-ui.textarea>
            </div>

            <div class="lg:col-span-2 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <label class="flex items-center gap-3 text-sm text-slate-600">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" checked class="h-4 w-4 rounded border-slate-300 text-slate-900 focus:ring-emerald-200">
                    <span>Produk aktif dan tampil di kasir</span>
                </label>

                <x-ui.button type="submit" variant="secondary">Simpan Produk</x-ui.button>
            </div>
        </form>
    </section>

    <section class="surface-card p-5">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="section-title">Daftar Produk</h2>
                <p class="section-copy mt-1">Edit produk langsung dari tabel. Perubahan stok harian tetap lewat menu Mutasi Stok.</p>
            </div>
            <x-ui.badge tone="dark">{{ $products->count() }} produk</x-ui.badge>
        </div>

        <div class="mt-5 overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 font-semibold text-slate-600">Produk</th>
                        <th class="px-4 py-3 font-semibold text-slate-600">Kategori</th>
                        <th class="px-4 py-3 font-semibold text-slate-600">Kode</th>
                        <th class="px-4 py-3 font-semibold text-slate-600">Lokasi</th>
                        <th class="px-4 py-3 font-semibold text-slate-600">Harga</th>
                        <th class="px-4 py-3 font-semibold text-slate-600">Stok</th>
                        <th class="px-4 py-3 font-semibold text-slate-600">Status</th>
                        <th class="px-4 py-3 font-semibold text-slate-600">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white">
                    @forelse ($products as $product)
                        <tr class="border-t-[12px] border-slate-100 odd:bg-white even:bg-slate-50/80 hover:bg-emerald-50/50">
                            <td class="px-4 py-3 align-top">
                                <form id="product-form-{{ $product->id }}" method="POST" action="{{ route('stock.products.update', $product) }}" class="grid min-w-64 gap-3">
                                    @csrf
                                    @method('PATCH')
                                    <div class="mb-1 inline-flex w-fit rounded-lg bg-slate-900 px-3 py-1 text-xs font-semibold uppercase tracking-[0.16em] text-white">
                                        Produk #{{ $loop->iteration }}
                                    </div>
                                    <x-ui.input name="name" :value="$product->name" :use-old="false" required />
                                    <x-ui.input name="unit" :value="$product->unit" :use-old="false" required />
                                    <textarea name="description" rows="2" class="field-textarea">{{ $product->description }}</textarea>
                                </form>
                            </td>
                            <td class="px-4 py-3 align-top">
                                <select name="category_id" form="product-form-{{ $product->id }}" class="field-select min-w-44" required>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}" @selected($product->category_id == $category->id)>{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="px-4 py-3 align-top">
                                <div class="grid min-w-44 gap-3">
                                    <input name="sku" value="{{ $product->sku }}" form="product-form-{{ $product->id }}" class="field-input" required>
                                    <input name="barcode" value="{{ $product->barcode }}" form="product-form-{{ $product->id }}" class="field-input" placeholder="Barcode">
                                    @if ($product->barcode)
                                        <a href="{{ route('stock.products.label', $product) }}" target="_blank" class="text-sm font-semibold text-emerald-700 hover:text-emerald-800">Cetak Label</a>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-3 align-top">
                                <input name="storage_location" value="{{ $product->storage_location }}" form="product-form-{{ $product->id }}" class="field-input min-w-56" placeholder="Etalase / rak / laci">
                            </td>
                            <td class="px-4 py-3 align-top">
                                <div class="grid min-w-36 gap-3">
                                    <input name="cost_price" type="number" step="0.01" min="0" value="{{ $product->cost_price }}" form="product-form-{{ $product->id }}" class="field-input" required>
                                    <input name="sale_price" type="number" step="0.01" min="0" value="{{ $product->sale_price }}" form="product-form-{{ $product->id }}" class="field-input" required>
                                </div>
                            </td>
                            <td class="px-4 py-3 align-top">
                                <div class="min-w-32">
                                    <x-ui.badge :tone="$product->isLowStock() ? 'warning' : 'success'">{{ $product->stock }} {{ $product->unit }}</x-ui.badge>
                                    <input name="minimum_stock" type="number" min="0" value="{{ $product->minimum_stock }}" form="product-form-{{ $product->id }}" class="field-input mt-3" required>
                                </div>
                            </td>
                            <td class="px-4 py-3 align-top">
                                <label class="flex items-center gap-2 text-slate-600">
                                    <input type="hidden" name="is_active" value="0" form="product-form-{{ $product->id }}">
                                    <input type="checkbox" name="is_active" value="1" form="product-form-{{ $product->id }}" @checked($product->is_active)>
                                    <span>{{ $product->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                                </label>
                            </td>
                            <td class="px-4 py-3 align-top">
                                <div class="flex flex-wrap gap-2">
                                    <x-ui.button type="submit" size="sm" form="product-form-{{ $product->id }}">Update</x-ui.button>
                                    <form method="POST" action="{{ route('stock.products.destroy', $product) }}" onsubmit="return confirm('Hapus produk ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <x-ui.button type="submit" size="sm" variant="danger">Hapus</x-ui.button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-8 text-center text-slate-500">
                                Belum ada produk. Tambahkan produk pertama agar modul kasir bisa mulai dipakai.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</x-layouts.app>
