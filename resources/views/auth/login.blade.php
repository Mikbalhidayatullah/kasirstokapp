<x-layouts.guest title="Masuk">
    <div>
        <p class="text-xs font-semibold uppercase tracking-[0.28em] text-slate-500">Masuk ke sistem</p>
        <h2 class="mt-3 text-3xl font-extrabold">Selamat datang kembali</h2>
        <p class="mt-3 text-sm leading-6 text-slate-500">
            Gunakan akun sesuai peran agar akses modul kasir dan stok tetap terpisah dengan jelas.
        </p>
    </div>

    <form method="POST" action="{{ route('login.store') }}" class="mt-8 space-y-5">
        @csrf

        <div>
            <x-ui.input label="Email" name="email" type="email" placeholder="contoh@toko.test" required />
        </div>

        <div>
            <x-ui.input label="Password" name="password" type="password" placeholder="Masukkan password" required />
        </div>

        <label class="flex items-center gap-3 text-sm text-slate-600">
            <input type="checkbox" name="remember" value="1" class="h-4 w-4 rounded border-slate-300 text-slate-900 focus:ring-amber-200">
            <span>Ingat sesi login saya</span>
        </label>

        <x-ui.button type="submit" variant="secondary" class="w-full">
            Masuk ke Dashboard
        </x-ui.button>
    </form>

    <div class="mt-8 rounded-3xl border border-dashed border-slate-200 bg-slate-50 p-5">
        <p class="text-sm font-semibold text-slate-700">Akun demo bawaan</p>
        <div class="mt-4 grid gap-3 text-sm text-slate-600">
            <div class="rounded-2xl bg-white p-4">
                <p class="font-semibold text-slate-900">Admin</p>
                <p class="mt-1">admin@kasirstok.test</p>
                <p>Password: <span class="font-semibold">password</span></p>
            </div>
            <div class="rounded-2xl bg-white p-4">
                <p class="font-semibold text-slate-900">Petugas Stok</p>
                <p class="mt-1">stok@kasirstok.test</p>
                <p>Password: <span class="font-semibold">password</span></p>
            </div>
            <div class="rounded-2xl bg-white p-4">
                <p class="font-semibold text-slate-900">Kasir</p>
                <p class="mt-1">kasir@kasirstok.test</p>
                <p>Password: <span class="font-semibold">password</span></p>
            </div>
        </div>
    </div>
</x-layouts.guest>
