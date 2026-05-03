<x-layouts.app
    title="Laporan Penjualan"
    heading="Laporan Penjualan {{ $periodLabel }}"
    description="Pilih rentang tanggal laporan, rekap metode pembayaran, lalu export hasilnya sesuai kebutuhan operasional."
>
    <x-slot:actions>
        <a href="{{ route('reports.sales.export', ['start_date' => $selectedStartDate, 'end_date' => $selectedEndDate, 'period' => $selectedPeriod, 'payment_method' => $selectedPaymentMethod]) }}">
            <x-ui.button variant="secondary">Export {{ $periodLabel }}</x-ui.button>
        </a>
    </x-slot:actions>

    <section class="surface-card p-5">
        <form method="GET" action="{{ route('reports.sales.index') }}" class="grid gap-4 lg:grid-cols-[170px_190px_190px_220px_auto] lg:items-end">
            <div>
                <x-ui.select label="Jenis laporan" name="period">
                    <option value="daily" @selected($selectedPeriod === 'daily')>Harian</option>
                    <option value="weekly" @selected($selectedPeriod === 'weekly')>Mingguan</option>
                    <option value="monthly" @selected($selectedPeriod === 'monthly')>Bulanan</option>
                    <option value="yearly" @selected($selectedPeriod === 'yearly')>Tahunan</option>
                </x-ui.select>
            </div>

            <div>
                <x-ui.input label="Tanggal mulai" name="start_date" type="date" :value="$selectedStartDate" />
            </div>

            <div>
                <x-ui.input label="Tanggal selesai" name="end_date" type="date" :value="$selectedEndDate" />
            </div>

            <div>
                <x-ui.select label="Metode pembayaran" name="payment_method">
                    <option value="">Semua metode</option>
                    @foreach ($paymentMethods as $method)
                        <option value="{{ $method->value }}" @selected($selectedPaymentMethod === $method->value)>{{ $method->label() }}</option>
                    @endforeach
                </x-ui.select>
            </div>

            <div class="flex flex-wrap gap-3">
                <x-ui.button type="submit">Terapkan Filter</x-ui.button>
                <a href="{{ route('reports.sales.index') }}">
                    <x-ui.button type="button" variant="ghost">Reset</x-ui.button>
                </a>
            </div>
        </form>

        <p class="mt-4 text-sm text-slate-500">
            Jenis laporan menentukan cara sistem merangkum grafik dan rekap, sementara tanggal mulai sampai selesai menentukan data transaksi yang diambil.
        </p>
    </section>

    <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <x-ui.stat-card label="Transaksi Terfilter" :value="$reportTotals->transactions ?? 0" hint="Jumlah transaksi sesuai filter aktif." />
        <x-ui.stat-card label="Dana Masuk Terfilter" :value="'Rp '.number_format($reportTotals->total_amount ?? 0, 0, ',', '.')" hint="Akumulasi grand total dari filter aktif." tone="dark" />
        <x-ui.stat-card label="Total Kembalian" :value="'Rp '.number_format($reportTotals->total_change ?? 0, 0, ',', '.')" hint="Jumlah kembalian transaksi tunai." />
        <x-ui.stat-card label="Rentang Periode" :value="$rangeLabel" hint="Rentang waktu laporan yang sedang direkap." />
    </section>

    <section class="grid gap-5 xl:grid-cols-[0.9fr_1.1fr]">
        <div class="space-y-5">
            <div class="surface-card p-5">
                <h2 class="section-title">Grafik Tren Penjualan</h2>
                <p class="section-copy mt-1">Visual ringkas pergerakan dana masuk pada periode {{ strtolower($periodLabel) }} yang sedang dipilih.</p>

                <div class="mt-6 overflow-x-auto">
                    <div class="flex min-h-[260px] min-w-max items-end gap-3 rounded-3xl bg-slate-50 p-5">
                        @forelse ($trend as $point)
                            <div class="flex w-16 flex-col items-center gap-3">
                                <div class="flex h-44 items-end">
                                    <div
                                        class="w-10 rounded-t-2xl bg-gradient-to-t from-amber-500 via-orange-400 to-emerald-400 shadow-[0_16px_28px_-18px_rgba(249,115,22,0.85)]"
                                        style="height: {{ $point['height'] }}%;"
                                        title="{{ $point['label'] }} - Rp {{ number_format($point['amount'], 0, ',', '.') }}"
                                    ></div>
                                </div>
                                <div class="text-center">
                                    <p class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">{{ $point['label'] }}</p>
                                    <p class="mt-1 text-[11px] text-slate-600">Rp {{ number_format($point['amount'], 0, ',', '.') }}</p>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-slate-500">Belum ada data tren untuk periode ini.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="surface-card p-5">
                <h2 class="section-title">Rekap Per Metode</h2>
                <p class="section-copy mt-1">Ringkasan dana masuk berdasarkan metode pembayaran pada periode {{ strtolower($periodLabel) }} terpilih.</p>

                <div class="mt-5 grid gap-3">
                    @foreach ($summary as $payment)
                    <div class="rounded-3xl border border-slate-100 bg-slate-50/70 p-4">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <p class="font-semibold text-slate-900">{{ $payment['label'] }}</p>
                                <p class="mt-1 text-sm text-slate-500">{{ $payment['transactions'] }} transaksi</p>
                            </div>
                            <x-ui.badge :tone="$payment['tone']">{{ $payment['label'] }}</x-ui.badge>
                        </div>
                        <p class="mt-3 text-lg font-extrabold text-slate-950">Rp {{ number_format($payment['total_amount'], 0, ',', '.') }}</p>
                    </div>
                @endforeach
            </div>
        </div>
        </div>

        <div class="surface-card p-5">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h2 class="section-title">Detail Transaksi</h2>
                    <p class="section-copy mt-1">Daftar transaksi yang masuk pada periode dan filter laporan aktif.</p>
                </div>
                <x-ui.badge tone="dark">{{ $sales->total() }} data</x-ui.badge>
            </div>

            <div class="mt-5 space-y-3">
                @forelse ($sales as $sale)
                    <div class="rounded-3xl border border-slate-100 bg-slate-50/70 p-4">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <p class="font-semibold text-slate-900">{{ $sale->invoice_number }}</p>
                                    <x-ui.badge :tone="$sale->payment_method->badgeTone()">{{ $sale->payment_method->label() }}</x-ui.badge>
                                </div>
                                <p class="mt-2 text-sm text-slate-500">
                                    {{ $sale->cashier->name }} - {{ $sale->sold_at->format('d M Y, H:i') }}
                                </p>
                            </div>

                            <div class="text-right">
                                <p class="text-sm text-slate-500">{{ $sale->total_items }} item</p>
                                <p class="text-lg font-extrabold text-slate-950">Rp {{ number_format($sale->grand_total, 0, ',', '.') }}</p>
                                <p class="mt-1 text-sm text-slate-500">Dana masuk Rp {{ number_format($sale->paid_amount, 0, ',', '.') }}</p>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="rounded-3xl border border-dashed border-slate-200 bg-slate-50 p-6 text-sm text-slate-500">
                        Belum ada transaksi pada filter yang dipilih.
                    </div>
                @endforelse
            </div>

            <div class="mt-5">
                {{ $sales->links() }}
            </div>
        </div>
    </section>
</x-layouts.app>
