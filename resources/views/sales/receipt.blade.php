<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Nota {{ $sale->invoice_number }} | {{ $appSettings['app_name'] ?? config('app.name') }}</title>
    @vite(['resources/css/app.css'])
    <style>
        @media print {
            .no-print {
                display: none !important;
            }

            body {
                background: #fff !important;
            }

            .receipt-paper {
                box-shadow: none !important;
                border: 0 !important;
                margin: 0 !important;
                max-width: 100% !important;
            }
        }
    </style>
</head>
<body class="px-4 py-6 sm:px-6">
    <div class="mx-auto max-w-3xl space-y-5">
        @if (session('status'))
            <div class="no-print surface-card border border-emerald-100 bg-emerald-50/80 p-4 text-sm text-emerald-800">
                {{ session('status') }}
            </div>
        @endif

        <div class="no-print surface-card flex flex-col gap-3 p-5 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.28em] text-slate-500">Nota Pembelian</p>
                <h1 class="mt-2 text-2xl font-extrabold text-slate-950">{{ $sale->invoice_number }}</h1>
                <p class="mt-2 text-sm text-slate-500">Transaksi selesai dan nota siap dicetak.</p>
            </div>

            <div class="flex flex-wrap gap-3">
                <a href="{{ route('cashier.index') }}">
                    <x-ui.button variant="ghost">Kembali ke Kasir</x-ui.button>
                </a>
                <a href="{{ route('sales.index') }}">
                    <x-ui.button variant="ghost">Riwayat Penjualan</x-ui.button>
                </a>
                <x-ui.button type="button" variant="secondary" onclick="window.print()">
                    Cetak Nota
                </x-ui.button>
            </div>
        </div>

        <section class="receipt-paper mx-auto max-w-2xl rounded-[32px] border border-slate-200 bg-white p-6 shadow-[0_30px_80px_-48px_rgba(15,23,42,0.45)] sm:p-8">
            <div class="border-b border-dashed border-slate-200 pb-6 text-center">
                <p class="text-xs font-semibold uppercase tracking-[0.32em] text-slate-500">Struk Belanja</p>
                <h2 class="mt-3 text-3xl font-extrabold text-slate-950">{{ $appSettings['app_name'] ?? config('app.name') }}</h2>
                <p class="mt-2 text-sm text-slate-500">Terima kasih sudah berbelanja</p>
            </div>

            <div class="grid gap-4 border-b border-dashed border-slate-200 py-6 sm:grid-cols-2">
                <div>
                    <p class="text-xs uppercase tracking-[0.22em] text-slate-500">Invoice</p>
                    <p class="mt-2 font-semibold text-slate-950">{{ $sale->invoice_number }}</p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-[0.22em] text-slate-500">Tanggal</p>
                    <p class="mt-2 font-semibold text-slate-950">{{ $sale->sold_at->format('d M Y, H:i') }}</p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-[0.22em] text-slate-500">Kasir</p>
                    <p class="mt-2 font-semibold text-slate-950">{{ $sale->cashier->name }}</p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-[0.22em] text-slate-500">Member</p>
                    <p class="mt-2 font-semibold text-slate-950">{{ $sale->member?->name ?? 'Pelanggan umum' }}</p>
                    @if ($sale->member)
                        <p class="mt-1 text-sm text-slate-500">Poin +{{ $sale->points_earned }} / -{{ $sale->points_redeemed }}</p>
                    @endif
                </div>
                <div>
                    <p class="text-xs uppercase tracking-[0.22em] text-slate-500">Total Item</p>
                    <p class="mt-2 font-semibold text-slate-950">{{ $sale->total_items }}</p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-[0.22em] text-slate-500">Pembayaran</p>
                    <p class="mt-2 font-semibold text-slate-950">{{ $sale->payment_method->label() }}</p>
                </div>
            </div>

            <div class="space-y-4 py-6">
                @foreach ($sale->items as $item)
                    <div class="flex items-start justify-between gap-4">
                        <div class="min-w-0">
                            <p class="font-semibold text-slate-950">{{ $item->product_name }}</p>
                            <p class="mt-1 text-sm text-slate-500">
                                {{ $item->quantity }} x Rp {{ number_format($item->price, 0, ',', '.') }}
                                <span class="text-slate-400">({{ $item->product_sku }})</span>
                            </p>
                        </div>
                        <p class="whitespace-nowrap font-semibold text-slate-950">
                            Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                        </p>
                    </div>
                @endforeach
            </div>

            <div class="space-y-3 border-t border-dashed border-slate-200 pt-6">
                <div class="flex items-center justify-between text-sm text-slate-600">
                    <span>Subtotal</span>
                    <span>Rp {{ number_format($sale->subtotal, 0, ',', '.') }}</span>
                </div>
                <div class="flex items-center justify-between text-sm text-slate-600">
                    <span>Diskon</span>
                    <span>Rp {{ number_format($sale->discount_amount, 0, ',', '.') }}</span>
                </div>
                <div class="flex items-center justify-between text-sm text-slate-600">
                    <span>Promo {{ $sale->promotion?->name ? '('.$sale->promotion->name.')' : '' }}</span>
                    <span>Rp {{ number_format($sale->promo_discount_amount, 0, ',', '.') }}</span>
                </div>
                <div class="flex items-center justify-between text-sm text-slate-600">
                    <span>Tukar Poin {{ $sale->pointReward?->name ? '('.$sale->pointReward->name.')' : '' }}</span>
                    <span>Rp {{ number_format($sale->point_discount_amount, 0, ',', '.') }}</span>
                </div>
                <div class="flex items-center justify-between text-sm text-slate-600">
                    <span>Pajak / Biaya</span>
                    <span>Rp {{ number_format($sale->tax_amount, 0, ',', '.') }}</span>
                </div>
                <div class="flex items-center justify-between border-t border-slate-100 pt-3 text-lg font-extrabold text-slate-950">
                    <span>Total Bayar</span>
                    <span>Rp {{ number_format($sale->grand_total, 0, ',', '.') }}</span>
                </div>
                <div class="flex items-center justify-between text-sm text-slate-600">
                    <span>Dana Masuk ({{ $sale->payment_method->label() }})</span>
                    <span>Rp {{ number_format($sale->paid_amount, 0, ',', '.') }}</span>
                </div>
                <div class="flex items-center justify-between text-sm font-semibold text-emerald-700">
                    <span>Kembalian</span>
                    <span>Rp {{ number_format($sale->change_amount, 0, ',', '.') }}</span>
                </div>
            </div>

            <div class="border-t border-dashed border-slate-200 pt-6 text-center">
                <p class="text-sm text-slate-500">Simpan nota ini sebagai bukti pembelian.</p>
                <p class="mt-2 text-xs uppercase tracking-[0.28em] text-slate-400">Terima kasih</p>
            </div>
        </section>
    </div>
</body>
</html>
