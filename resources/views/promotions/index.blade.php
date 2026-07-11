<x-layouts.app
    title="Promo & Poin"
    heading="Pengaturan Promo dan Poin"
    description="Atur promo event secara manual, aktifkan saat diperlukan, dan kelola daftar reward poin yang bisa ditukar di kasir."
>
    <section class="space-y-5">
        <div class="surface-card p-5">
            <h2 class="section-title">Tambah Promo Manual</h2>
            <p class="section-copy mt-1">Promo aktif akan muncul di kasir. Matikan jika event sudah selesai.</p>

            <form method="POST" action="{{ route('promotions.store') }}" class="mt-5 grid gap-4 md:grid-cols-2">
                @csrf

                <div class="md:col-span-2">
                    <x-ui.input label="Nama promo" name="name" placeholder="Contoh: Weekend Vape Fest" required />
                </div>

                <div>
                    <x-ui.select label="Jenis diskon" name="discount_type" required>
                        <option value="percentage">Persentase</option>
                        <option value="fixed">Nominal Rupiah</option>
                    </x-ui.select>
                </div>

                <div>
                    <x-ui.input label="Nilai diskon" name="discount_value" type="number" min="0.01" step="0.01" placeholder="10 atau 25000" required />
                </div>

                <div class="md:col-span-2">
                    <x-ui.textarea label="Catatan" name="notes" rows="3" placeholder="Keterangan event, syarat promo, atau periode promo"></x-ui.textarea>
                </div>

                <div class="md:col-span-2 flex flex-wrap items-center justify-between gap-3">
                    <div class="flex flex-wrap gap-4">
                        <label class="flex items-center gap-3 text-sm text-slate-600">
                            <input type="hidden" name="member_only" value="0">
                            <input type="checkbox" name="member_only" value="1" class="h-4 w-4 rounded border-slate-300 text-slate-900 focus:ring-emerald-200">
                            <span>Khusus member</span>
                        </label>
                        <label class="flex items-center gap-3 text-sm text-slate-600">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" value="1" checked class="h-4 w-4 rounded border-slate-300 text-slate-900 focus:ring-emerald-200">
                            <span>Aktif</span>
                        </label>
                    </div>

                    <x-ui.button type="submit" variant="secondary">Simpan Promo</x-ui.button>
                </div>
            </form>
        </div>

        <div class="surface-card p-5">
            <h2 class="section-title">Tambah Reward Poin</h2>
            <p class="section-copy mt-1">Poin member bertambah otomatis: Rp 1.000 belanja menghasilkan 1 poin.</p>

            <form method="POST" action="{{ route('point-rewards.store') }}" class="mt-5 grid gap-4 md:grid-cols-2">
                @csrf

                <div class="md:col-span-2">
                    <x-ui.input label="Nama reward" name="name" placeholder="Contoh: Tukar 50 Poin" required />
                </div>

                <div>
                    <x-ui.input label="Biaya poin" name="points_cost" type="number" min="1" placeholder="50" required />
                </div>

                <div>
                    <x-ui.input label="Potongan rupiah" name="discount_amount" type="number" min="0.01" step="0.01" placeholder="10000" required />
                </div>

                <div class="md:col-span-2">
                    <x-ui.textarea label="Catatan" name="notes" rows="3" placeholder="Keterangan reward atau aturan penukaran"></x-ui.textarea>
                </div>

                <div class="md:col-span-2 flex flex-wrap items-center justify-between gap-3">
                    <label class="flex items-center gap-3 text-sm text-slate-600">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" checked class="h-4 w-4 rounded border-slate-300 text-slate-900 focus:ring-emerald-200">
                        <span>Aktif</span>
                    </label>

                    <x-ui.button type="submit" variant="secondary">Simpan Reward</x-ui.button>
                </div>
            </form>
        </div>
    </section>

    <section class="surface-card p-5">
        <h2 class="section-title">Daftar Promo</h2>
        <div class="mt-5 overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 font-semibold text-slate-600">Promo</th>
                        <th class="px-4 py-3 font-semibold text-slate-600">Diskon</th>
                        <th class="px-4 py-3 font-semibold text-slate-600">Scope</th>
                        <th class="px-4 py-3 font-semibold text-slate-600">Status</th>
                        <th class="px-4 py-3 font-semibold text-slate-600">Dipakai</th>
                        <th class="px-4 py-3 font-semibold text-slate-600">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse ($promotions as $promotion)
                        <tr>
                            <td class="px-4 py-3 align-top">
                                <form id="promotion-form-{{ $promotion->id }}" method="POST" action="{{ route('promotions.update', $promotion) }}" class="grid gap-3">
                                    @csrf
                                    @method('PATCH')
                                    <x-ui.input name="name" :value="$promotion->name" :use-old="false" required />
                                    <x-ui.textarea name="notes" rows="2" :value="$promotion->notes" :use-old="false"></x-ui.textarea>
                                </form>
                            </td>
                            <td class="px-4 py-3 align-top">
                                <div class="grid min-w-52 gap-3">
                                    <select name="discount_type" form="promotion-form-{{ $promotion->id }}" class="field-select">
                                        <option value="percentage" @selected($promotion->discount_type === 'percentage')>Persentase</option>
                                        <option value="fixed" @selected($promotion->discount_type === 'fixed')>Nominal</option>
                                    </select>
                                    <input name="discount_value" form="promotion-form-{{ $promotion->id }}" type="number" min="0.01" step="0.01" value="{{ $promotion->discount_value }}" class="field-input">
                                </div>
                            </td>
                            <td class="px-4 py-3 align-top">
                                <label class="flex items-center gap-2 text-slate-600">
                                    <input type="hidden" name="member_only" value="0" form="promotion-form-{{ $promotion->id }}">
                                    <input type="checkbox" name="member_only" value="1" form="promotion-form-{{ $promotion->id }}" @checked($promotion->member_only)>
                                    <span>{{ $promotion->member_only ? 'Khusus member' : 'Semua pelanggan' }}</span>
                                </label>
                            </td>
                            <td class="px-4 py-3 align-top">
                                <label class="flex items-center gap-2 text-slate-600">
                                    <input type="hidden" name="is_active" value="0" form="promotion-form-{{ $promotion->id }}">
                                    <input type="checkbox" name="is_active" value="1" form="promotion-form-{{ $promotion->id }}" @checked($promotion->is_active)>
                                    <span>{{ $promotion->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                                </label>
                            </td>
                            <td class="px-4 py-3 align-top text-slate-600">{{ $promotion->sales_count }} transaksi</td>
                            <td class="px-4 py-3 align-top">
                                <div class="flex flex-wrap gap-2">
                                    <x-ui.button type="submit" size="sm" form="promotion-form-{{ $promotion->id }}">Update</x-ui.button>
                                    <form method="POST" action="{{ route('promotions.destroy', $promotion) }}" onsubmit="return confirm('Hapus promo ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <x-ui.button type="submit" size="sm" variant="danger">Hapus</x-ui.button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-slate-500">Belum ada promo.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section class="surface-card p-5">
        <h2 class="section-title">Daftar Reward Poin</h2>
        <div class="mt-5 overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 font-semibold text-slate-600">Reward</th>
                        <th class="px-4 py-3 font-semibold text-slate-600">Poin</th>
                        <th class="px-4 py-3 font-semibold text-slate-600">Potongan</th>
                        <th class="px-4 py-3 font-semibold text-slate-600">Status</th>
                        <th class="px-4 py-3 font-semibold text-slate-600">Dipakai</th>
                        <th class="px-4 py-3 font-semibold text-slate-600">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse ($pointRewards as $reward)
                        <tr>
                            <td class="px-4 py-3 align-top">
                                <form id="reward-form-{{ $reward->id }}" method="POST" action="{{ route('point-rewards.update', $reward) }}" class="grid gap-3">
                                    @csrf
                                    @method('PATCH')
                                    <x-ui.input name="name" :value="$reward->name" :use-old="false" required />
                                    <x-ui.textarea name="notes" rows="2" :value="$reward->notes" :use-old="false"></x-ui.textarea>
                                </form>
                            </td>
                            <td class="px-4 py-3 align-top">
                                <input name="points_cost" form="reward-form-{{ $reward->id }}" type="number" min="1" value="{{ $reward->points_cost }}" class="field-input min-w-28">
                            </td>
                            <td class="px-4 py-3 align-top">
                                <input name="discount_amount" form="reward-form-{{ $reward->id }}" type="number" min="0.01" step="0.01" value="{{ $reward->discount_amount }}" class="field-input min-w-36">
                            </td>
                            <td class="px-4 py-3 align-top">
                                <label class="flex items-center gap-2 text-slate-600">
                                    <input type="hidden" name="is_active" value="0" form="reward-form-{{ $reward->id }}">
                                    <input type="checkbox" name="is_active" value="1" form="reward-form-{{ $reward->id }}" @checked($reward->is_active)>
                                    <span>{{ $reward->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                                </label>
                            </td>
                            <td class="px-4 py-3 align-top text-slate-600">{{ $reward->sales_count }} transaksi</td>
                            <td class="px-4 py-3 align-top">
                                <div class="flex flex-wrap gap-2">
                                    <x-ui.button type="submit" size="sm" form="reward-form-{{ $reward->id }}">Update</x-ui.button>
                                    <form method="POST" action="{{ route('point-rewards.destroy', $reward) }}" onsubmit="return confirm('Hapus reward ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <x-ui.button type="submit" size="sm" variant="danger">Hapus</x-ui.button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-slate-500">Belum ada reward poin.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</x-layouts.app>
