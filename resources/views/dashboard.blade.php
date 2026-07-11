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

    <section class="grid gap-4 lg:grid-cols-2 xl:grid-cols-4">
        <div class="surface-card p-5">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.22em] text-slate-500">Kesehatan Stok</p>
                    <p class="mt-3 text-3xl font-extrabold text-slate-950">{{ $stats['stock_health_percent'] }}%</p>
                </div>
                <x-ui.badge :tone="$stats['low_stock_count'] > 0 ? 'warning' : 'success'">{{ $stats['low_stock_count'] }} tipis</x-ui.badge>
            </div>
            <div class="mt-5 h-3 overflow-hidden rounded-full bg-slate-100">
                <div class="h-full rounded-full bg-emerald-500" style="width: {{ $stats['stock_health_percent'] }}%;"></div>
            </div>
            <div class="mt-3 flex items-center justify-between text-sm text-slate-500">
                <span>{{ $stats['healthy_stock_count'] }} aman</span>
                <span>{{ $stats['product_count'] }} produk</span>
            </div>
        </div>

        <div class="surface-card p-5">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.22em] text-slate-500">Member Aktif</p>
                    <p class="mt-3 text-3xl font-extrabold text-slate-950">{{ $stats['active_member_percent'] }}%</p>
                </div>
                <x-ui.badge tone="success">{{ $stats['member_count'] }} aktif</x-ui.badge>
            </div>
            <div class="mt-5 h-3 overflow-hidden rounded-full bg-slate-100">
                <div class="h-full rounded-full bg-emerald-500" style="width: {{ $stats['active_member_percent'] }}%;"></div>
            </div>
            <div class="mt-3 flex items-center justify-between text-sm text-slate-500">
                <span>{{ $stats['member_count'] }} bisa dipilih kasir</span>
                <span>{{ $stats['total_member_count'] }} total</span>
            </div>
        </div>

        <div class="surface-card p-5">
            <p class="text-xs font-semibold uppercase tracking-[0.22em] text-slate-500">Transaksi Hari Ini</p>
            <div class="mt-4 flex items-end justify-between gap-4">
                <p class="text-4xl font-extrabold text-slate-950">{{ $stats['today_transactions'] }}</p>
                <div class="flex h-16 items-end gap-1">
                    @foreach ([35, 52, 42, 68, 58, 76, max(12, min(100, $stats['today_transactions'] * 18))] as $height)
                        <span class="w-3 rounded-t bg-slate-900" style="height: {{ $height }}%;"></span>
                    @endforeach
                </div>
            </div>
            <p class="mt-3 text-sm text-slate-500">Jumlah transaksi selesai yang masuk hari ini.</p>
        </div>

        <div class="surface-card bg-slate-950 p-5 text-white">
            <p class="text-xs font-semibold uppercase tracking-[0.22em] text-white/60">Pendapatan Hari Ini</p>
            <p class="mt-4 text-3xl font-extrabold text-white">Rp {{ number_format($stats['today_revenue'], 0, ',', '.') }}</p>
            <div class="mt-5 grid gap-2">
                @foreach ($paymentBreakdown as $payment)
                    <div>
                        <div class="flex items-center justify-between text-xs text-white/70">
                            <span>{{ $payment['label'] }}</span>
                            <span>{{ $payment['percent'] }}%</span>
                        </div>
                        <div class="mt-1 h-2 overflow-hidden rounded-full bg-white/10">
                            <div class="h-full rounded-full bg-emerald-400" style="width: {{ $payment['percent'] }}%;"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="surface-card p-5">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="section-title">Tren Penjualan 7 Hari</h2>
                <p class="section-copy mt-1">Bar chart pendapatan harian agar performa minggu ini cepat terbaca.</p>
            </div>
            <x-ui.badge tone="dark">7 hari</x-ui.badge>
        </div>

        <div class="mt-6 overflow-x-auto">
            <div class="flex min-h-64 min-w-max items-end gap-4 rounded-lg bg-slate-50 p-5">
                @foreach ($salesTrend as $point)
                    <div class="flex w-20 flex-col items-center gap-3">
                        <div class="flex h-40 items-end">
                            <div
                                class="w-10 rounded-t-lg bg-emerald-500 shadow-[0_16px_28px_-18px_rgba(16,185,129,0.8)]"
                                style="height: {{ $point['height'] }}%;"
                                title="{{ $point['label'] }} - Rp {{ number_format($point['amount'], 0, ',', '.') }}"
                            ></div>
                        </div>
                        <div class="text-center">
                            <p class="text-xs font-semibold text-slate-600">{{ $point['label'] }}</p>
                            <p class="mt-1 text-[11px] text-slate-500">Rp {{ number_format($point['amount'], 0, ',', '.') }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="grid gap-5 xl:grid-cols-[1.25fr_0.75fr]">
        <div class="surface-card p-5">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h2 class="section-title">Transaksi Terbaru</h2>
                    <p class="section-copy mt-1">Area ini menampilkan maksimal empat transaksi, lalu bisa digulir.</p>
                </div>
                <x-ui.badge tone="dark">{{ $recentSales->count() }} data</x-ui.badge>
            </div>

            <div class="mt-5 max-h-[468px] space-y-3 overflow-y-auto pr-2">
                @forelse ($recentSales as $sale)
                    <div class="rounded-lg border border-slate-100 bg-slate-50/70 p-4">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <p class="font-semibold text-slate-900">{{ $sale->invoice_number }}</p>
                                <p class="mt-1 text-sm text-slate-500">
                                    {{ $sale->cashier->name }} - {{ $sale->sold_at->format('d M Y, H:i') }}
                                </p>
                                @if ($sale->member)
                                    <p class="mt-1 text-xs font-semibold uppercase tracking-[0.18em] text-emerald-700">
                                        Member {{ $sale->member->name }}
                                    </p>
                                @endif
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
                        <div class="rounded-lg border border-slate-100 bg-slate-50/70 p-4">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <p class="font-semibold text-slate-900">{{ $payment['label'] }}</p>
                                    <p class="mt-1 text-sm text-slate-500">{{ $payment['transactions'] }} transaksi</p>
                                </div>
                                <x-ui.badge :tone="$payment['tone']">{{ $payment['percent'] }}%</x-ui.badge>
                            </div>
                            <p class="mt-3 text-lg font-extrabold text-slate-950">Rp {{ number_format($payment['amount'], 0, ',', '.') }}</p>
                            <div class="mt-3 h-2 overflow-hidden rounded-full bg-white">
                                <div class="h-full rounded-full bg-emerald-500" style="width: {{ $payment['percent'] }}%;"></div>
                            </div>
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
        </div>
    </section>

    <section class="surface-card p-5">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="section-title">Mutasi Stok Terakhir</h2>
                <p class="section-copy mt-1">Perubahan stok masuk, penyesuaian, dan penjualan terbaru dalam panel full-width.</p>
            </div>
            <x-ui.badge tone="dark">{{ $recentMovements->count() }} data</x-ui.badge>
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
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse ($recentMovements as $movement)
                        <tr>
                            <td class="px-4 py-3 text-slate-600">{{ $movement->occurred_at->format('d M Y, H:i') }}</td>
                            <td class="px-4 py-3 font-semibold text-slate-900">{{ $movement->product->name }}</td>
                            <td class="px-4 py-3">
                                <x-ui.badge :tone="$movement->quantity < 0 ? 'danger' : 'success'">{{ $movement->type->label() }}</x-ui.badge>
                            </td>
                            <td class="px-4 py-3 font-extrabold {{ $movement->quantity < 0 ? 'text-rose-600' : 'text-emerald-600' }}">
                                {{ $movement->quantity > 0 ? '+' : '' }}{{ $movement->quantity }}
                            </td>
                            <td class="px-4 py-3 text-slate-600">{{ $movement->previous_stock }} -> {{ $movement->current_stock }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $movement->user?->name ?? 'Sistem' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-slate-500">Belum ada mutasi stok.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</x-layouts.app>
