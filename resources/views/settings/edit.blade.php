<x-layouts.app
    title="Pengaturan"
    heading="Pengaturan Aplikasi"
    description="Ubah nama aplikasi, logo, dan warna tema website dari satu halaman."
>
    <section class="surface-card p-5">
        <h2 class="section-title">Identitas dan Tema</h2>
        <p class="section-copy mt-1">Perubahan akan dipakai di header, login, tombol utama, dan warna latar aplikasi.</p>

        <form method="POST" action="{{ route('settings.update') }}" enctype="multipart/form-data" class="mt-5 grid gap-5 lg:grid-cols-[1fr_0.7fr]">
            @csrf
            @method('PATCH')

            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <x-ui.input label="Nama aplikasi" name="app_name" :value="$settings['app_name']" :use-old="false" required />
                </div>

                <div>
                    <x-ui.input label="Label kecil" name="app_tagline" :value="$settings['app_tagline']" :use-old="false" required />
                </div>

                <div>
                    <label for="theme_primary" class="field-label">Warna utama</label>
                    <input id="theme_primary" name="theme_primary" type="color" value="{{ $settings['theme_primary'] }}" class="h-12 w-full rounded-lg border border-slate-200 bg-white p-1">
                </div>

                <div>
                    <label for="theme_accent" class="field-label">Warna aksen tombol</label>
                    <input id="theme_accent" name="theme_accent" type="color" value="{{ $settings['theme_accent'] }}" class="h-12 w-full rounded-lg border border-slate-200 bg-white p-1">
                </div>

                <div>
                    <label for="theme_background" class="field-label">Warna background</label>
                    <input id="theme_background" name="theme_background" type="color" value="{{ $settings['theme_background'] }}" class="h-12 w-full rounded-lg border border-slate-200 bg-white p-1">
                </div>

                <div>
                    <label for="logo" class="field-label">Upload logo</label>
                    <input id="logo" name="logo" type="file" accept="image/*" class="field-input">
                </div>
            </div>

            <div class="rounded-lg border border-slate-200 bg-slate-50 p-5">
                <p class="text-sm font-semibold text-slate-700">Preview</p>
                <div class="mt-4 rounded-lg p-5 text-white" style="background-color: {{ $settings['theme_primary'] }};">
                    <div class="flex items-center gap-3">
                        <div class="flex h-14 w-14 items-center justify-center rounded-lg bg-white/12 text-lg font-extrabold text-white">
                            @if ($settings['logo_path'])
                                <img src="{{ asset($settings['logo_path']) }}" alt="{{ $settings['app_name'] }}" class="h-full w-full rounded-lg object-cover">
                            @else
                                {{ Str::of($settings['app_name'])->explode(' ')->map(fn ($word) => Str::substr($word, 0, 1))->take(2)->join('') }}
                            @endif
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.3em]" style="color: {{ $settings['theme_accent'] }};">{{ $settings['app_tagline'] }}</p>
                            <p class="mt-1 text-xl font-extrabold">{{ $settings['app_name'] }}</p>
                        </div>
                    </div>
                    <button type="button" class="mt-5 rounded-lg px-4 py-3 text-sm font-semibold text-slate-950" style="background-color: {{ $settings['theme_accent'] }};">Contoh Tombol</button>
                </div>
            </div>

            <div class="lg:col-span-2 flex justify-end">
                <x-ui.button type="submit" variant="secondary">Simpan Pengaturan</x-ui.button>
            </div>
        </form>
    </section>
</x-layouts.app>
