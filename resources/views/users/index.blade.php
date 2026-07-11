<x-layouts.app
    title="Manajemen User"
    heading="Manajemen User dan Role"
    description="Kelola akun admin, petugas stok, dan kasir. Nonaktifkan user tanpa menghapus histori transaksi."
>
    <section class="surface-card p-5">
        <h2 class="section-title">Tambah User Baru</h2>
        <p class="section-copy mt-1">User aktif bisa langsung login sesuai role yang dipilih.</p>

        <form method="POST" action="{{ route('users.store') }}" class="mt-5 grid gap-4 lg:grid-cols-4">
            @csrf

            <div>
                <x-ui.input label="Nama" name="name" placeholder="Nama user" required />
            </div>

            <div>
                <x-ui.input label="Email" name="email" type="email" placeholder="user@toko.test" required />
            </div>

            <div>
                <x-ui.select label="Role" name="role" required>
                    @foreach ($roles as $role)
                        <option value="{{ $role->value }}" @selected(old('role') === $role->value)>{{ $role->label() }}</option>
                    @endforeach
                </x-ui.select>
            </div>

            <div>
                <x-ui.input label="Password" name="password" type="password" placeholder="Minimal 8 karakter" required />
            </div>

            <div class="lg:col-span-4 flex flex-wrap items-center justify-between gap-3">
                <label class="flex items-center gap-3 text-sm text-slate-600">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" checked class="h-4 w-4 rounded border-slate-300 text-slate-900 focus:ring-emerald-200">
                    <span>User aktif</span>
                </label>

                <x-ui.button type="submit" variant="secondary">Simpan User</x-ui.button>
            </div>
        </form>
    </section>

    <section class="surface-card p-5">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="section-title">Daftar User</h2>
                <p class="section-copy mt-1">Edit nama, email, role, status, dan reset password dari tabel.</p>
            </div>
            <x-ui.badge tone="dark">{{ $users->count() }} user</x-ui.badge>
        </div>

        <div class="mt-5 overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 font-semibold text-slate-600">User</th>
                        <th class="px-4 py-3 font-semibold text-slate-600">Role</th>
                        <th class="px-4 py-3 font-semibold text-slate-600">Password Baru</th>
                        <th class="px-4 py-3 font-semibold text-slate-600">Status</th>
                        <th class="px-4 py-3 font-semibold text-slate-600">Histori</th>
                        <th class="px-4 py-3 font-semibold text-slate-600">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse ($users as $managedUser)
                        @php
                            $isCurrentUser = auth()->id() === $managedUser->id;
                        @endphp
                        <tr class="hover:bg-emerald-50/30">
                            <td class="px-4 py-3 align-top">
                                <form id="user-form-{{ $managedUser->id }}" method="POST" action="{{ route('users.update', $managedUser) }}" class="grid min-w-64 gap-3">
                                    @csrf
                                    @method('PATCH')
                                    <x-ui.input name="name" :value="$managedUser->name" :use-old="false" required />
                                    <x-ui.input name="email" type="email" :value="$managedUser->email" :use-old="false" required />
                                </form>
                            </td>
                            <td class="px-4 py-3 align-top">
                                <select name="role" form="user-form-{{ $managedUser->id }}" class="field-select min-w-44" required>
                                    @foreach ($roles as $role)
                                        <option value="{{ $role->value }}" @selected($managedUser->role === $role)>{{ $role->label() }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="px-4 py-3 align-top">
                                <input name="password" form="user-form-{{ $managedUser->id }}" type="password" minlength="8" placeholder="Kosongkan jika tetap" class="field-input min-w-52">
                            </td>
                            <td class="px-4 py-3 align-top">
                                <label class="flex items-center gap-2 text-slate-600">
                                    <input type="hidden" name="is_active" value="{{ $isCurrentUser ? 1 : 0 }}" form="user-form-{{ $managedUser->id }}">
                                    @if ($isCurrentUser)
                                        <input type="checkbox" name="is_active" value="1" form="user-form-{{ $managedUser->id }}" @checked($managedUser->is_active) disabled>
                                    @else
                                        <input type="checkbox" name="is_active" value="1" form="user-form-{{ $managedUser->id }}" @checked($managedUser->is_active)>
                                    @endif
                                    <span>{{ $managedUser->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                                </label>
                            </td>
                            <td class="px-4 py-3 align-top text-slate-600">
                                <p>{{ $managedUser->sales_count }} transaksi</p>
                                <p class="mt-1">{{ $managedUser->stock_movements_count }} mutasi</p>
                            </td>
                            <td class="px-4 py-3 align-top">
                                <div class="flex flex-wrap gap-2">
                                    <x-ui.button type="submit" size="sm" form="user-form-{{ $managedUser->id }}">Update</x-ui.button>
                                    @if ($isCurrentUser)
                                        <x-ui.button type="button" size="sm" variant="danger" disabled>Hapus</x-ui.button>
                                    @else
                                        <form method="POST" action="{{ route('users.destroy', $managedUser) }}" onsubmit="return confirm('Hapus user ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <x-ui.button type="submit" size="sm" variant="danger">Hapus</x-ui.button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-slate-500">Belum ada user.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</x-layouts.app>
