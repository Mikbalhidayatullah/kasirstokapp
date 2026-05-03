<x-layouts.app
    title="Produk"
    heading="Inventaris Produk"
    description="Kelola SKU, harga, batas minimum stok, dan status aktif produk dari satu halaman."
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
                <x-ui.input label="SKU" name="sku" placeholder="Contoh: MKN-002" required />
            </div>

            <div>
                <x-ui.input label="Barcode" name="barcode" placeholder="Scan atau input barcode barang" />
            </div>

            <div>
                <x-ui.input label="Nama produk" name="name" placeholder="Nama barang" required />
            </div>

            <div>
                <x-ui.input label="Satuan" name="unit" placeholder="pcs / box / gelas" required />
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
                <x-ui.textarea label="Deskripsi" name="description" rows="3" placeholder="Catatan produk jika diperlukan"></x-ui.textarea>
            </div>

            <div class="lg:col-span-2 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <label class="flex items-center gap-3 text-sm text-slate-600">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" checked class="h-4 w-4 rounded border-slate-300 text-slate-900 focus:ring-amber-200">
                    <span>Produk aktif dan tampil di kasir</span>
                </label>

                <x-ui.button type="submit" variant="secondary">
                    Simpan Produk
                </x-ui.button>
            </div>
        </form>
    </section>

    <section class="grid gap-4 xl:grid-cols-2">
        @forelse ($products as $product)
            <div class="surface-card p-5">
                <form method="POST" action="{{ route('stock.products.update', $product) }}">
                    @csrf
                    @method('PATCH')

                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">{{ $product->sku }}</p>
                            <h2 class="mt-2 text-xl font-extrabold">{{ $product->name }}</h2>
                        </div>

                        <x-ui.badge :tone="$product->isLowStock() ? 'warning' : 'success'">
                            {{ $product->isLowStock() ? 'Stok Tipis' : 'Aman' }}
                        </x-ui.badge>
                    </div>

                    <div class="mt-5 grid gap-4 md:grid-cols-2">
                        <div>
                            <x-ui.select label="Kategori" name="category_id" required>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}" @selected($product->category_id == $category->id)>{{ $category->name }}</option>
                                @endforeach
                            </x-ui.select>
                        </div>

                        <div>
                            <x-ui.input label="SKU" name="sku" :value="$product->sku" :use-old="false" required />
                        </div>

                        <div>
                            <x-ui.input label="Barcode" name="barcode" :value="$product->barcode" :use-old="false" placeholder="Scan atau input barcode barang" />
                        </div>

                        <div>
                            <x-ui.input label="Nama produk" name="name" :value="$product->name" :use-old="false" required />
                        </div>

                        <div>
                            <x-ui.input label="Satuan" name="unit" :value="$product->unit" :use-old="false" required />
                        </div>

                        <div>
                            <x-ui.input label="Harga modal" name="cost_price" type="number" step="0.01" min="0" :value="$product->cost_price" :use-old="false" required />
                        </div>

                        <div>
                            <x-ui.input label="Harga jual" name="sale_price" type="number" step="0.01" min="0" :value="$product->sale_price" :use-old="false" required />
                        </div>

                        <div>
                            <x-ui.input label="Minimum stok" name="minimum_stock" type="number" min="0" :value="$product->minimum_stock" :use-old="false" required />
                        </div>

                        <div class="rounded-3xl border border-dashed border-slate-200 bg-slate-50 p-4">
                            <p class="text-xs font-semibold uppercase tracking-[0.22em] text-slate-500">Stok saat ini</p>
                            <p class="mt-2 text-2xl font-extrabold text-slate-950">{{ $product->stock }} {{ $product->unit }}</p>
                            <p class="mt-2 text-sm leading-6 text-slate-500">
                                Perubahan stok harian dilakukan dari menu Mutasi Stok agar histori tetap tercatat.
                            </p>
                        </div>

                        <div class="md:col-span-2">
                            <x-ui.textarea label="Deskripsi" name="description" rows="3" :value="$product->description" :use-old="false"></x-ui.textarea>
                        </div>
                    </div>

                    <div class="mt-5 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <label class="flex items-center gap-3 text-sm text-slate-600">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" value="1" @checked($product->is_active) class="h-4 w-4 rounded border-slate-300 text-slate-900 focus:ring-amber-200">
                            <span>Produk aktif</span>
                        </label>

                        <div class="flex flex-wrap items-center gap-3">
                            @if ($product->barcode)
                                <span class="rounded-full bg-slate-100 px-3 py-2 text-xs font-semibold uppercase tracking-[0.18em] text-slate-600">
                                    Barcode {{ $product->barcode }}
                                </span>
                                <a href="{{ route('stock.products.label', $product) }}" target="_blank">
                                    <x-ui.button type="button" size="sm" variant="ghost">Cetak Label</x-ui.button>
                                </a>
                            @endif
                            <x-ui.button type="submit" size="sm">Update</x-ui.button>
                        </div>
                    </div>
                </form>

                <form method="POST" action="{{ route('stock.products.destroy', $product) }}" class="mt-3" onsubmit="return confirm('Hapus produk ini?')">
                    @csrf
                    @method('DELETE')
                    <x-ui.button type="submit" size="sm" variant="danger">Hapus</x-ui.button>
                </form>
            </div>
        @empty
            <div class="surface-card border border-dashed border-slate-200 p-8 text-sm text-slate-500 xl:col-span-2">
                Belum ada produk. Tambahkan produk pertama agar modul kasir bisa mulai dipakai.
            </div>
        @endforelse
    </section>
</x-layouts.app>
