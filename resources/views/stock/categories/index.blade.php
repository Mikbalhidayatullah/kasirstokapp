<x-layouts.app
    title="Kategori"
    heading="Kategori Produk"
    description="Kelompokkan produk agar inventaris dan tampilan kasir lebih mudah dikelola."
>
    <section class="grid gap-5 xl:grid-cols-[0.82fr_1.18fr]">
        <div class="surface-card p-5">
            <h2 class="section-title">Tambah Kategori</h2>
            <p class="section-copy mt-1">Isi data dasar kategori untuk produk yang akan dijual.</p>

            <form method="POST" action="{{ route('stock.categories.store') }}" class="mt-5 space-y-4">
                @csrf

                <div>
                    <x-ui.input label="Nama kategori" name="name" placeholder="Contoh: Minuman" required />
                </div>

                <div>
                    <x-ui.textarea label="Deskripsi" name="description" rows="4" placeholder="Penjelasan singkat kategori"></x-ui.textarea>
                </div>

                <label class="flex items-center gap-3 text-sm text-slate-600">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" checked class="h-4 w-4 rounded border-slate-300 text-slate-900 focus:ring-amber-200">
                    <span>Aktifkan kategori ini</span>
                </label>

                <x-ui.button type="submit" variant="secondary" class="w-full">
                    Simpan Kategori
                </x-ui.button>
            </form>
        </div>

        <div class="space-y-4">
            @forelse ($categories as $category)
                <div class="surface-card p-5">
                    <form method="POST" action="{{ route('stock.categories.update', $category) }}">
                        @csrf
                        @method('PATCH')

                        <div class="grid gap-4 lg:grid-cols-[1fr_1.2fr_auto] lg:items-start">
                            <div>
                                <x-ui.input label="Nama kategori" name="name" :value="$category->name" :use-old="false" required />
                            </div>

                            <div>
                                <x-ui.textarea label="Deskripsi" name="description" rows="3" :value="$category->description" :use-old="false"></x-ui.textarea>
                            </div>

                            <div class="space-y-3">
                                <div class="rounded-2xl bg-slate-50 p-4 text-sm text-slate-600">
                                    <p class="font-semibold text-slate-900">{{ $category->products_count }} produk</p>
                                    <p class="mt-1">Terakhir diubah {{ $category->updated_at->format('d M Y') }}</p>
                                </div>

                                <label class="flex items-center gap-3 text-sm text-slate-600">
                                    <input type="hidden" name="is_active" value="0">
                                    <input type="checkbox" name="is_active" value="1" @checked($category->is_active) class="h-4 w-4 rounded border-slate-300 text-slate-900 focus:ring-amber-200">
                                    <span>Aktif</span>
                                </label>

                                <x-ui.button type="submit" size="sm">Update</x-ui.button>
                            </div>
                        </div>
                    </form>

                    <form method="POST" action="{{ route('stock.categories.destroy', $category) }}" class="mt-3" onsubmit="return confirm('Hapus kategori ini?')">
                        @csrf
                        @method('DELETE')
                        <x-ui.button type="submit" size="sm" variant="danger">Hapus</x-ui.button>
                    </form>
                </div>
            @empty
                <div class="surface-card border border-dashed border-slate-200 p-8 text-sm text-slate-500">
                    Belum ada kategori. Tambahkan kategori pertama untuk mulai mengelompokkan produk.
                </div>
            @endforelse
        </div>
    </section>
</x-layouts.app>
