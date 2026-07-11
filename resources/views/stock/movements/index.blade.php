<x-layouts.app
    title="Mutasi Stok"
    heading="Mutasi dan Penyesuaian Stok"
    description="Catat stok masuk manual dan penyesuaian inventaris sambil menjaga histori perubahan tetap lengkap."
>
    <section class="space-y-5">
        <div class="surface-card p-5">
            <h2 class="section-title">Input Mutasi Baru</h2>
            <p class="section-copy mt-1">Gunakan stok masuk untuk penambahan barang, atau penyesuaian untuk koreksi manual.</p>

            <form method="POST" action="{{ route('stock.movements.store') }}" class="mt-5 grid gap-4 lg:grid-cols-4 lg:items-end">
                @csrf

                <div>
                    <x-ui.select label="Produk" name="product_id" required>
                        <option value="">Pilih produk</option>
                        @foreach ($products as $product)
                            <option value="{{ $product->id }}" @selected(old('product_id') == $product->id)>
                                {{ $product->name }} - stok {{ $product->stock }}
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
                    <x-ui.input label="Jumlah" name="quantity" type="number" placeholder="Angka positif atau negatif untuk penyesuaian" required />
                </div>

                <div class="lg:col-span-3">
                    <x-ui.textarea label="Catatan" name="note" rows="3" placeholder="Alasan perubahan stok"></x-ui.textarea>
                </div>

                <x-ui.button type="submit" variant="secondary" class="w-full">Simpan Mutasi</x-ui.button>
            </form>
        </div>

        <div class="surface-card p-5">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h2 class="section-title">Riwayat Mutasi</h2>
                    <p class="section-copy mt-1">Semua perubahan stok tersimpan di tabel, termasuk penjualan dari kasir.</p>
                </div>
                <x-ui.badge tone="dark">{{ $movements->total() }} data</x-ui.badge>
            </div>

            <div class="mt-5 overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-3 font-semibold text-slate-600">Waktu</th>
                            <th class="px-4 py-3 font-semibold text-slate-600">Produk</th>
                            <th class="px-4 py-3 font-semibold text-slate-600">Jenis</th>
                            <th class="px-4 py-3 font-semibold text-slate-600">Perubahan</th>
                            <th class="px-4 py-3 font-semibold text-slate-600">Stok</th>
                            <th class="px-4 py-3 font-semibold text-slate-600">User</th>
                            <th class="px-4 py-3 font-semibold text-slate-600">Catatan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse ($movements as $movement)
                            <tr>
                                <td class="px-4 py-3 align-top text-slate-600">{{ $movement->occurred_at->format('d M Y, H:i') }}</td>
                                <td class="px-4 py-3 align-top font-semibold text-slate-900">{{ $movement->product->name }}</td>
                                <td class="px-4 py-3 align-top">
                                    <x-ui.badge :tone="$movement->quantity < 0 ? 'danger' : 'success'">{{ $movement->type->label() }}</x-ui.badge>
                                </td>
                                <td class="px-4 py-3 align-top font-extrabold {{ $movement->quantity < 0 ? 'text-rose-600' : 'text-emerald-600' }}">
                                    {{ $movement->quantity > 0 ? '+' : '' }}{{ $movement->quantity }}
                                </td>
                                <td class="px-4 py-3 align-top text-slate-600">{{ $movement->previous_stock }} -> {{ $movement->current_stock }}</td>
                                <td class="px-4 py-3 align-top text-slate-600">{{ $movement->user?->name ?? 'Sistem' }}</td>
                                <td class="px-4 py-3 align-top text-slate-600">{{ $movement->note ?: '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-center text-slate-500">Belum ada mutasi stok yang tercatat.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-5">
                {{ $movements->links() }}
            </div>
        </div>
    </section>
</x-layouts.app>
