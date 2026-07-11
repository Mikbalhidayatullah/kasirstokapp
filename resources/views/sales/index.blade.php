<x-layouts.app
    title="Riwayat Penjualan"
    heading="Riwayat Penjualan"
    description="Lihat transaksi selesai, member, promo, poin, metode pembayaran, dan buka nota kapan saja."
>
    <section class="surface-card p-5">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="section-title">Daftar Transaksi</h2>
                <p class="section-copy mt-1">Format tabel lebih ringkas untuk histori transaksi yang panjang.</p>
            </div>
            <x-ui.badge tone="dark">{{ $sales->total() }} data</x-ui.badge>
        </div>

        <div class="mt-5 overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 font-semibold text-slate-600">Invoice</th>
                        <th class="px-4 py-3 font-semibold text-slate-600">Tanggal</th>
                        <th class="px-4 py-3 font-semibold text-slate-600">Member</th>
                        <th class="px-4 py-3 font-semibold text-slate-600">Item</th>
                        <th class="px-4 py-3 font-semibold text-slate-600">Promo/Poin</th>
                        <th class="px-4 py-3 font-semibold text-slate-600">Pembayaran</th>
                        <th class="px-4 py-3 font-semibold text-slate-600">Total</th>
                        <th class="px-4 py-3 font-semibold text-slate-600">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse ($sales as $sale)
                        <tr>
                            <td class="px-4 py-3 align-top">
                                <p class="font-semibold text-slate-950">{{ $sale->invoice_number }}</p>
                                <p class="mt-1 text-slate-500">{{ $sale->cashier->name }}</p>
                            </td>
                            <td class="px-4 py-3 align-top text-slate-600">{{ $sale->sold_at->format('d M Y, H:i') }}</td>
                            <td class="px-4 py-3 align-top">
                                @if ($sale->member)
                                    <p class="font-semibold text-slate-900">{{ $sale->member->name }}</p>
                                    <p class="mt-1 text-slate-500">{{ $sale->member->phone_number }}</p>
                                    <p class="mt-1 text-xs font-semibold text-emerald-700">+{{ $sale->points_earned }} / -{{ $sale->points_redeemed }} poin</p>
                                @else
                                    <span class="text-slate-500">Umum</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 align-top">
                                <p class="font-semibold text-slate-900">{{ $sale->total_items }} item</p>
                                <p class="mt-1 max-w-xs text-slate-500">
                                    {{ $sale->items->map(fn ($item) => $item->product_name.' x'.$item->quantity)->join(', ') }}
                                </p>
                            </td>
                            <td class="px-4 py-3 align-top text-slate-600">
                                <p>{{ $sale->promotion?->name ?? 'Tanpa promo' }}</p>
                                <p class="mt-1">Promo: Rp {{ number_format($sale->promo_discount_amount, 0, ',', '.') }}</p>
                                <p>Reward: Rp {{ number_format($sale->point_discount_amount, 0, ',', '.') }}</p>
                            </td>
                            <td class="px-4 py-3 align-top">
                                <x-ui.badge :tone="$sale->payment_method->badgeTone()">{{ $sale->payment_method->label() }}</x-ui.badge>
                                <p class="mt-2 text-slate-500">Masuk Rp {{ number_format($sale->paid_amount, 0, ',', '.') }}</p>
                            </td>
                            <td class="px-4 py-3 align-top">
                                <p class="font-extrabold text-slate-950">Rp {{ number_format($sale->grand_total, 0, ',', '.') }}</p>
                                <p class="mt-1 text-slate-500">Kembali Rp {{ number_format($sale->change_amount, 0, ',', '.') }}</p>
                            </td>
                            <td class="px-4 py-3 align-top">
                                <a href="{{ route('sales.receipt', $sale) }}">
                                    <x-ui.button variant="ghost" size="sm">Nota</x-ui.button>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-8 text-center text-slate-500">
                                Belum ada riwayat penjualan yang bisa ditampilkan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-5">
            {{ $sales->links() }}
        </div>
    </section>
</x-layouts.app>
