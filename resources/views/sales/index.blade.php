<x-layouts.app
    title="Riwayat Penjualan"
    heading="Riwayat Penjualan"
    description="Lihat transaksi yang sudah selesai, item yang terjual, metode pembayarannya, dan buka nota kapan saja untuk dicetak ulang."
>
    <section class="space-y-4">
        @forelse ($sales as $sale)
            <article class="surface-card p-5">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                    <div>
                        <div class="flex flex-wrap items-center gap-3">
                            <h2 class="text-xl font-extrabold">{{ $sale->invoice_number }}</h2>
                            <x-ui.badge tone="dark">{{ $sale->total_items }} item</x-ui.badge>
                            <x-ui.badge :tone="$sale->payment_method->badgeTone()">{{ $sale->payment_method->label() }}</x-ui.badge>
                        </div>
                        <p class="mt-2 text-sm text-slate-500">
                            {{ $sale->cashier->name }} - {{ $sale->sold_at->format('d M Y, H:i') }}
                        </p>
                    </div>

                    <div class="flex flex-col gap-3 lg:items-end">
                        <div class="grid gap-3 text-right sm:grid-cols-3">
                            <div class="rounded-2xl bg-slate-50 px-4 py-3">
                                <p class="text-xs uppercase tracking-[0.22em] text-slate-500">Subtotal</p>
                                <p class="mt-2 font-extrabold text-slate-950">Rp {{ number_format($sale->subtotal, 0, ',', '.') }}</p>
                            </div>
                            <div class="rounded-2xl bg-slate-50 px-4 py-3">
                                <p class="text-xs uppercase tracking-[0.22em] text-slate-500">Grand Total</p>
                                <p class="mt-2 font-extrabold text-slate-950">Rp {{ number_format($sale->grand_total, 0, ',', '.') }}</p>
                            </div>
                            <div class="rounded-2xl bg-emerald-50 px-4 py-3">
                                <p class="text-xs uppercase tracking-[0.22em] text-emerald-600">Kembalian</p>
                                <p class="mt-2 font-extrabold text-emerald-700">Rp {{ number_format($sale->change_amount, 0, ',', '.') }}</p>
                            </div>
                        </div>

                        <a href="{{ route('sales.receipt', $sale) }}">
                            <x-ui.button variant="ghost" size="sm">Buka Nota</x-ui.button>
                        </a>
                    </div>
                </div>

                <div class="mt-5 overflow-hidden rounded-3xl border border-slate-100">
                    <table class="min-w-full divide-y divide-slate-100 text-sm">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-4 py-3 font-semibold text-slate-500">Produk</th>
                                <th class="px-4 py-3 font-semibold text-slate-500">SKU</th>
                                <th class="px-4 py-3 font-semibold text-slate-500">Qty</th>
                                <th class="px-4 py-3 font-semibold text-slate-500">Harga</th>
                                <th class="px-4 py-3 font-semibold text-slate-500">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @foreach ($sale->items as $item)
                                <tr>
                                    <td class="px-4 py-3 font-medium text-slate-900">{{ $item->product_name }}</td>
                                    <td class="px-4 py-3 text-slate-500">{{ $item->product_sku }}</td>
                                    <td class="px-4 py-3 text-slate-500">{{ $item->quantity }}</td>
                                    <td class="px-4 py-3 text-slate-500">Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                                    <td class="px-4 py-3 font-semibold text-slate-900">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </article>
        @empty
            <div class="surface-card border border-dashed border-slate-200 p-8 text-sm text-slate-500">
                Belum ada riwayat penjualan yang bisa ditampilkan.
            </div>
        @endforelse
    </section>

    <div>
        {{ $sales->links() }}
    </div>
</x-layouts.app>
