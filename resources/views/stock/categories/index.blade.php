<x-layouts.app
    title="Kategori"
    heading="Kategori Produk"
    description="Kelompokkan produk agar inventaris dan tampilan kasir lebih mudah dikelola."
>
    <section class="space-y-5">
        <div class="surface-card p-5">
            <h2 class="section-title">Tambah Kategori</h2>
            <p class="section-copy mt-1">Isi data dasar kategori untuk produk yang akan dijual.</p>

            <form method="POST" action="{{ route('stock.categories.store') }}" class="mt-5 grid gap-4 lg:grid-cols-[1fr_2fr_auto] lg:items-end">
                @csrf

                <div>
                    <x-ui.input label="Nama kategori" name="name" placeholder="Contoh: Liquid" required />
                </div>

                <div>
                    <x-ui.textarea label="Deskripsi" name="description" rows="3" placeholder="Penjelasan singkat kategori"></x-ui.textarea>
                </div>

                <div class="space-y-3">
                    <label class="flex items-center gap-3 text-sm text-slate-600">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" checked class="h-4 w-4 rounded border-slate-300 text-slate-900 focus:ring-emerald-200">
                        <span>Aktif</span>
                    </label>

                    <x-ui.button type="submit" variant="secondary" class="w-full">Simpan</x-ui.button>
                </div>
            </form>
        </div>

        <div class="surface-card p-5">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h2 class="section-title">Daftar Kategori</h2>
                    <p class="section-copy mt-1">Edit kategori langsung dari tabel.</p>
                </div>
                <x-ui.badge tone="dark">{{ $categories->count() }} kategori</x-ui.badge>
            </div>

            <div class="mt-5 overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-3 font-semibold text-slate-600">Kategori</th>
                            <th class="px-4 py-3 font-semibold text-slate-600">Deskripsi</th>
                            <th class="px-4 py-3 font-semibold text-slate-600">Produk</th>
                            <th class="px-4 py-3 font-semibold text-slate-600">Status</th>
                            <th class="px-4 py-3 font-semibold text-slate-600">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse ($categories as $category)
                            <tr>
                                <td class="px-4 py-3 align-top">
                                    <form id="category-form-{{ $category->id }}" method="POST" action="{{ route('stock.categories.update', $category) }}">
                                        @csrf
                                        @method('PATCH')
                                        <x-ui.input name="name" :value="$category->name" :use-old="false" required />
                                    </form>
                                </td>
                                <td class="px-4 py-3 align-top">
                                    <textarea name="description" form="category-form-{{ $category->id }}" rows="3" class="field-textarea min-w-72">{{ $category->description }}</textarea>
                                </td>
                                <td class="px-4 py-3 align-top text-slate-600">
                                    <p class="font-semibold text-slate-900">{{ $category->products_count }} produk</p>
                                    <p class="mt-1">Update {{ $category->updated_at->format('d M Y') }}</p>
                                </td>
                                <td class="px-4 py-3 align-top">
                                    <label class="flex items-center gap-2 text-slate-600">
                                        <input type="hidden" name="is_active" value="0" form="category-form-{{ $category->id }}">
                                        <input type="checkbox" name="is_active" value="1" form="category-form-{{ $category->id }}" @checked($category->is_active)>
                                        <span>{{ $category->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                                    </label>
                                </td>
                                <td class="px-4 py-3 align-top">
                                    <div class="flex flex-wrap gap-2">
                                        <x-ui.button type="submit" size="sm" form="category-form-{{ $category->id }}">Update</x-ui.button>
                                        <form method="POST" action="{{ route('stock.categories.destroy', $category) }}" onsubmit="return confirm('Hapus kategori ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <x-ui.button type="submit" size="sm" variant="danger">Hapus</x-ui.button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-slate-500">
                                    Belum ada kategori. Tambahkan kategori pertama untuk mulai mengelompokkan produk.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</x-layouts.app>
