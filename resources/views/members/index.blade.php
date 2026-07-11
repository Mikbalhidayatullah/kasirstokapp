<x-layouts.app
    title="Membership"
    heading="Membership WhatsApp"
    description="Kelola pelanggan tetap, saldo poin, histori belanja, dan shortcut chat WhatsApp."
>
    <section class="surface-card p-5">
        <h2 class="section-title">Tambah Member Baru</h2>
        <p class="section-copy mt-1">Poin otomatis bertambah saat transaksi: setiap Rp 1.000 grand total menghasilkan 1 poin.</p>

        <form method="POST" action="{{ route('members.store') }}" class="mt-5 grid gap-4 lg:grid-cols-3">
            @csrf

            <div>
                <x-ui.input label="Nama member" name="name" placeholder="Contoh: Rizky Vapor" required />
            </div>

            <div>
                <x-ui.input label="Nomor WhatsApp" name="phone_number" placeholder="Contoh: 081234567890" required />
            </div>

            <div>
                <x-ui.input label="Tanggal lahir" name="birth_date" type="date" />
            </div>

            <div>
                <x-ui.input label="Saldo poin awal" name="points_balance" type="number" min="0" value="0" />
            </div>

            <div class="lg:col-span-2">
                <x-ui.input label="Catatan" name="notes" placeholder="Preferensi liquid, device, atau catatan pelayanan" />
            </div>

            <div class="lg:col-span-3 flex flex-wrap items-center justify-between gap-3">
                <label class="flex items-center gap-3 text-sm text-slate-600">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" checked class="h-4 w-4 rounded border-slate-300 text-slate-900 focus:ring-emerald-200">
                    <span>Member aktif</span>
                </label>

                <x-ui.button type="submit" variant="secondary">Simpan Member</x-ui.button>
            </div>
        </form>
    </section>

    <section class="surface-card p-5">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="section-title">Daftar Member</h2>
                <p class="section-copy mt-1">Edit data langsung dari tabel agar tetap nyaman saat member sudah banyak.</p>
            </div>
            <x-ui.badge tone="dark">{{ $members->count() }} member</x-ui.badge>
        </div>

        <div class="mt-5 overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 font-semibold text-slate-600">Member</th>
                        <th class="px-4 py-3 font-semibold text-slate-600">WhatsApp</th>
                        <th class="px-4 py-3 font-semibold text-slate-600">Poin</th>
                        <th class="px-4 py-3 font-semibold text-slate-600">Histori</th>
                        <th class="px-4 py-3 font-semibold text-slate-600">Status</th>
                        <th class="px-4 py-3 font-semibold text-slate-600">Catatan</th>
                        <th class="px-4 py-3 font-semibold text-slate-600">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse ($members as $member)
                        <tr>
                            <td class="px-4 py-3 align-top">
                                <form id="member-form-{{ $member->id }}" method="POST" action="{{ route('members.update', $member) }}" class="grid min-w-52 gap-3">
                                    @csrf
                                    @method('PATCH')
                                    <x-ui.input name="name" :value="$member->name" :use-old="false" required />
                                    <input name="birth_date" type="date" value="{{ $member->birth_date?->toDateString() }}" form="member-form-{{ $member->id }}" class="field-input">
                                </form>
                            </td>
                            <td class="px-4 py-3 align-top">
                                <div class="grid min-w-48 gap-3">
                                    <input name="phone_number" value="{{ $member->phone_number }}" form="member-form-{{ $member->id }}" class="field-input" required>
                                    <a class="text-sm font-semibold text-emerald-700 hover:text-emerald-800" href="{{ $member->whatsapp_url }}" target="_blank" rel="noopener">Chat WhatsApp</a>
                                </div>
                            </td>
                            <td class="px-4 py-3 align-top">
                                <input name="points_balance" type="number" min="0" value="{{ $member->points_balance }}" form="member-form-{{ $member->id }}" class="field-input min-w-28">
                            </td>
                            <td class="px-4 py-3 align-top text-slate-600">
                                <p class="font-semibold text-slate-900">{{ $member->sales_count }} transaksi</p>
                                <p class="mt-1">Rp {{ number_format($member->sales_sum_grand_total ?? 0, 0, ',', '.') }}</p>
                            </td>
                            <td class="px-4 py-3 align-top">
                                <label class="flex items-center gap-2 text-slate-600">
                                    <input type="hidden" name="is_active" value="0" form="member-form-{{ $member->id }}">
                                    <input type="checkbox" name="is_active" value="1" form="member-form-{{ $member->id }}" @checked($member->is_active)>
                                    <span>{{ $member->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                                </label>
                            </td>
                            <td class="px-4 py-3 align-top">
                                <textarea name="notes" form="member-form-{{ $member->id }}" rows="3" class="field-textarea min-w-64">{{ $member->notes }}</textarea>
                            </td>
                            <td class="px-4 py-3 align-top">
                                <div class="flex flex-wrap gap-2">
                                    <x-ui.button type="submit" size="sm" form="member-form-{{ $member->id }}">Update</x-ui.button>
                                    <form method="POST" action="{{ route('members.destroy', $member) }}" onsubmit="return confirm('Hapus member ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <x-ui.button type="submit" size="sm" variant="danger">Hapus</x-ui.button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-slate-500">
                                Belum ada member. Tambahkan pelanggan pertama agar histori dan poin bisa mulai tercatat.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</x-layouts.app>
