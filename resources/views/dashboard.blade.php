<x-layouts.app
    title="Dashboard"
    heading="Ringkasan Operasional"
    description="Pantau performa penjualan, sumber dana masuk, pergerakan stok, dan titik kritis inventaris dari satu layar utama."
>
    <x-slot:actions>
        @if (auth()->user()->canHandleCashier())
            <a href="{{ route('cashier.index') }}">
                <x-ui.button variant="secondary">Buka Kasir</x-ui.button>
            </a>
        @endif
    </x-slot:actions>

    <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <x-ui.stat-card label="Total Produk" :value="$stats['product_count']" hint="Jumlah item aktif yang terdaftar di inventaris." />
        <x-ui.stat-card label="Stok Menipis" :value="$stats['low_stock_count']" hint="Produk yang stoknya sudah menyentuh batas minimum." tone="warning" />
        <x-ui.stat-card label="Transaksi Hari Ini" :value="$stats['today_transactions']" hint="Jumlah transaksi yang selesai diproses hari ini." />
        <x-ui.stat-card label="Pendapatan Hari Ini" :value="'Rp '.number_format($stats['today_revenue'], 0, ',', '.')" hint="Akumulasi grand total transaksi hari ini." tone="dark" />
    </section>

    <section class="grid gap-5 xl:grid-cols-[1.3fr_0.7fr]">
        <div class="surface-card p-5">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h2 class="section-title">Transaksi Terbaru</h2>
                    <p class="section-copy mt-1">Lima transaksi terakhir yang tercatat di kasir.</p>
                </div>
                <x-ui.badge tone="dark">{{ $recentSales->count() }} data</x-ui.badge>
            </div>

            <div class="mt-5 space-y-3">
                @forelse ($recentSales as $sale)
                    <div class="rounded-3xl border border-slate-100 bg-slate-50/70 p-4">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <p class="font-semibold text-slate-900">{{ $sale->invoice_number }}</p>
                                <p class="mt-1 text-sm text-slate-500">
                                    {{ $sale->cashier->name }} - {{ $sale->sold_at->format('d M Y, H:i') }}
                                </p>
                            </div>
                            <div class="text-right">
                                <div class="mb-2">
                                    <x-ui.badge :tone="$sale->payment_method->badgeTone()">{{ $sale->payment_method->label() }}</x-ui.badge>
                                </div>
                                <p class="text-sm text-slate-500">{{ $sale->total_items }} item</p>
                                <p class="text-lg font-extrabold text-slate-950">Rp {{ number_format($sale->grand_total, 0, ',', '.') }}</p>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="rounded-3xl border border-dashed border-slate-200 bg-slate-50 p-6 text-sm text-slate-500">
                        Belum ada transaksi yang tercatat.
                    </div>
                @endforelse
            </div>
        </div>

        <div class="space-y-5">
            <div class="surface-card p-5">
                <h2 class="section-title">Dana Masuk Hari Ini</h2>
                <p class="section-copy mt-1">Pembagian transaksi berdasarkan metode pembayaran yang dipakai hari ini.</p>

                <div class="mt-5 grid gap-3">
                    @foreach ($paymentBreakdown as $payment)
                        <div class="rounded-3xl border border-slate-100 bg-slate-50/70 p-4">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <p class="font-semibold text-slate-900">{{ $payment['label'] }}</p>
                                    <p class="mt-1 text-sm text-slate-500">{{ $payment['transactions'] }} transaksi</p>
                                </div>
                                <x-ui.badge :tone="$payment['tone']">{{ $payment['label'] }}</x-ui.badge>
                            </div>
                            <p class="mt-3 text-lg font-extrabold text-slate-950">Rp {{ number_format($payment['amount'], 0, ',', '.') }}</p>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="surface-card p-5">
                <h2 class="section-title">Produk Terlaris</h2>
                <p class="section-copy mt-1">Diurutkan dari kuantitas penjualan tertinggi.</p>

                <div class="mt-5 space-y-4">
                    @forelse ($topProducts as $product)
                        <div>
                            <div class="flex items-center justify-between gap-3 text-sm">
                                <span class="font-semibold text-slate-700">{{ $product->name }}</span>
                                <span class="text-slate-500">{{ $product->sold_quantity }} terjual</span>
                            </div>
                            <div class="mt-2 h-2 rounded-full bg-slate-100">
                                <div class="h-2 rounded-full bg-gradient-to-r from-amber-400 to-orange-500" style="width: {{ max(12, min(100, (int) $product->sold_quantity * 12)) }}%;"></div>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-slate-500">Grafik penjualan akan muncul setelah transaksi pertama masuk.</p>
                    @endforelse
                </div>
            </div>

            <div class="surface-card p-5">
                <h2 class="section-title">Mutasi Stok Terakhir</h2>
                <p class="section-copy mt-1">Perubahan stok masuk, penyesuaian, dan penjualan terbaru.</p>

                <div class="mt-5 space-y-3">
                    @forelse ($recentMovements as $movement)
                        <div class="rounded-3xl border border-slate-100 p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="font-semibold text-slate-900">{{ $movement->product->name }}</p>
                                    <p class="mt-1 text-sm text-slate-500">
                                        {{ $movement->user?->name ?? 'Sistem' }} - {{ $movement->occurred_at->format('d M Y, H:i') }}
                                    </p>
                                </div>
                                <x-ui.badge :tone="$movement->quantity < 0 ? 'danger' : 'success'">{{ $movement->type->label() }}</x-ui.badge>
                            </div>
                            <p class="mt-3 text-sm text-slate-600">
                                {{ $movement->previous_stock }} -> {{ $movement->current_stock }}
                                <span class="font-semibold {{ $movement->quantity < 0 ? 'text-rose-600' : 'text-emerald-600' }}">
                                    ({{ $movement->quantity > 0 ? '+' : '' }}{{ $movement->quantity }})
                                </span>
                            </p>
                        </div>
                    @empty
                        <div class="rounded-3xl border border-dashed border-slate-200 bg-slate-50 p-6 text-sm text-slate-500">
                            Belum ada mutasi stok.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </section>
</x-layouts.app>
