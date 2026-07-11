@props(['title' => 'Masuk'])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }} | {{ $appSettings['app_name'] ?? config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body
    class="flex items-center justify-center px-4 py-10"
    style="--theme-primary: {{ $appSettings['theme_primary'] ?? '#020617' }}; --theme-accent: {{ $appSettings['theme_accent'] ?? '#f59e0b' }}; --theme-background: {{ $appSettings['theme_background'] ?? '#f8fafc' }};"
>
    <div class="grid w-full max-w-5xl gap-6 lg:grid-cols-[1.05fr_0.95fr]">
        <section class="surface-dark flex flex-col justify-between p-8 lg:p-10">
            <div>
                <x-app-logo variant="light" />
                <p class="mt-8 max-w-md text-4xl font-extrabold leading-tight">
                    Satu dashboard untuk transaksi kasir dan kontrol stok yang tetap sinkron.
                </p>
                <p class="mt-4 max-w-lg text-sm leading-7 text-white/72">
                    Dirancang untuk toko yang butuh alur cepat di meja kasir tanpa kehilangan visibilitas stok harian.
                </p>
            </div>

            <div class="grid gap-3 sm:grid-cols-3">
                <div class="rounded-2xl bg-white/8 p-4">
                    <p class="text-xs uppercase tracking-[0.24em] text-white/55">Transaksi</p>
                    <p class="mt-2 text-2xl font-bold">Cepat</p>
                </div>
                <div class="rounded-2xl bg-white/8 p-4">
                    <p class="text-xs uppercase tracking-[0.24em] text-white/55">Stok</p>
                    <p class="mt-2 text-2xl font-bold">Terkontrol</p>
                </div>
                <div class="rounded-2xl bg-white/8 p-4">
                    <p class="text-xs uppercase tracking-[0.24em] text-white/55">Akses</p>
                    <p class="mt-2 text-2xl font-bold">Terpisah</p>
                </div>
            </div>
        </section>

        <section class="surface-card p-8 lg:p-10">
            {{ $slot }}
        </section>
    </div>
</body>
</html>
