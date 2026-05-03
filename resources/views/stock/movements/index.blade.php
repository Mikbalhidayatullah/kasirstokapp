<x-layouts.app
    title="Mutasi Stok"
    heading="Mutasi dan Penyesuaian Stok"
    description="Catat stok masuk manual dan penyesuaian inventaris sambil menjaga histori perubahan tetap lengkap."
>
    <section class="grid gap-5 xl:grid-cols-[0.78fr_1.22fr]">
        <div class="surface-card p-5">
            <h2 class="section-title">Input Mutasi Baru</h2>
            <p class="section-copy mt-1">Gunakan stok masuk untuk penambahan barang, atau penyesuaian untuk koreksi manual.</p>

            <form method="POST" action="{{ route('stock.movements.store') }}" class="mt-5 space-y-4">
                @csrf

                <div>
                    <x-ui.select label="Produk" name="product_id" required>
                        <option value="">Pilih produk</option>
                        @foreach ($products as $product)
                            <option value="{{ $product->id }}" @selected(old('product_id') == $product->id)>
                                {{ $product->name }} • stok {{ $product->stock }}
                            </option>
                        @endforeach
                    </x-ui.select>
                </div>

                <div>
                    <x-ui.select label="Jenis mutasi" name="type" required>
                        <option value="stock_in" @selected(old('type') === 'stock_in')>Stok Masuk</option>
                        <option value="adjustment" @selected(old('type') === 'adjustment')>Penyesuaian</option>
                    </x-ui.select>
                </div>

                <div>
                    <x-ui.input
                        label="Jumlah"
                        name="quantity"
                        type="number"
                        placeholder="Isi angka positif untuk stok masuk, angka negatif/positif untuk penyesuaian"
                        required
                    />
                </div>

                <div>
                    <x-ui.textarea label="Catatan" name="note" rows="4" placeholder="Alasan perubahan stok"></x-ui.textarea>
                </div>

                <x-ui.button type="submit" variant="secondary" class="w-full">
                    Simpan Mutasi
                </x-ui.button>
            </form>
        </div>

        <div class="surface-card p-5">
            <h2 class="section-title">Riwayat Mutasi</h2>
            <p class="section-copy mt-1">Semua perubahan stok tersimpan di sini, termasuk penjualan dari kasir.</p>

            <div class="mt-5 space-y-3">
                @forelse ($movements as $movement)
                    <div class="rounded-3xl border border-slate-100 bg-slate-50/60 p-4">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <p class="font-semibold text-slate-900">{{ $movement->product->name }}</p>
                                    <x-ui.badge :tone="$movement->quantity < 0 ? 'danger' : 'success'">
                                        {{ $movement->type->label() }}
                                    </x-ui.badge>
                                </div>
                                <p class="mt-2 text-sm text-slate-500">
                                    {{ $movement->user?->name ?? 'Sistem' }} • {{ $movement->occurred_at->format('d M Y, H:i') }}
                                </p>
                                @if ($movement->note)
                                    <p class="mt-3 text-sm leading-6 text-slate-600">{{ $movement->note }}</p>
                                @endif
                            </div>

                            <div class="rounded-2xl bg-white px-4 py-3 text-right">
                                <p class="text-xs uppercase tracking-[0.22em] text-slate-500">Perubahan</p>
                                <p class="mt-1 text-lg font-extrabold {{ $movement->quantity < 0 ? 'text-rose-600' : 'text-emerald-600' }}">
                                    {{ $movement->quantity > 0 ? '+' : '' }}{{ $movement->quantity }}
                                </p>
                                <p class="mt-1 text-sm text-slate-500">{{ $movement->previous_stock }} → {{ $movement->current_stock }}</p>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="rounded-3xl border border-dashed border-slate-200 bg-slate-50 p-6 text-sm text-slate-500">
                        Belum ada mutasi stok yang tercatat.
                    </div>
                @endforelse
            </div>

            <div class="mt-5">
                {{ $movements->links() }}
            </div>
        </div>
    </section>
</x-layouts.app>
