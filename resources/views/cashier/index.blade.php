@php
    $oldItems = old('items', [['product_id' => '', 'quantity' => 1]]);
    $productCatalog = $products->map(fn ($product) => [
        'id' => $product->id,
        'name' => $product->name,
        'sku' => $product->sku,
        'barcode' => $product->barcode,
        'category' => $product->category->name,
        'location' => $product->storage_location,
        'unit' => $product->unit,
        'price' => (float) $product->sale_price,
        'stock' => $product->stock,
    ])->values();
    $memberCatalog = $members->map(fn ($member) => [
        'id' => $member->id,
        'name' => $member->name,
        'phone' => $member->phone_number,
        'points' => $member->points_balance,
    ])->values();
    $promotionCatalog = $promotions->map(fn ($promotion) => [
        'id' => $promotion->id,
        'name' => $promotion->name,
        'type' => $promotion->discount_type,
        'value' => (float) $promotion->discount_value,
        'memberOnly' => $promotion->member_only,
    ])->values();
    $rewardCatalog = $pointRewards->map(fn ($reward) => [
        'id' => $reward->id,
        'name' => $reward->name,
        'points' => $reward->points_cost,
        'discount' => (float) $reward->discount_amount,
    ])->values();
    $selectedMember = $members->firstWhere('id', (int) old('member_id'));
@endphp

<x-layouts.app
    title="Kasir"
    heading="Meja Kasir"
    description="Checkout liquid, device, coil, pod, dan aksesoris dengan scan barcode, pilihan member, serta info lokasi barang."
>
    <section class="space-y-5">
        <div class="surface-card p-5">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h2 class="section-title">Buat Transaksi Baru</h2>
                    <p class="section-copy mt-1">Tambahkan item, pilih metode bayar, lalu proses transaksi dengan cepat.</p>
                </div>
                <x-ui.badge tone="dark">{{ $products->count() }} produk</x-ui.badge>
            </div>

            <form method="POST" action="{{ route('cashier.checkout') }}" class="mt-5 space-y-5" id="checkout-form">
                @csrf

                <div class="rounded-lg border border-emerald-100 bg-emerald-50/70 p-4">
                    <input id="member_id" name="member_id" type="hidden" value="{{ old('member_id') }}">
                    <div class="flex flex-col gap-3 lg:flex-row lg:items-end">
                        <div class="flex-1">
                            <label for="member-phone-input" class="field-label">Nomor WhatsApp / HP Member</label>
                            <input
                                id="member-phone-input"
                                type="tel"
                                autocomplete="off"
                                inputmode="numeric"
                                placeholder="Ketik nomor HP, contoh 081234567890"
                                value="{{ $selectedMember?->phone_number }}"
                                class="field-input"
                            >
                        </div>
                        <x-ui.button type="button" variant="ghost" id="clear-member-selection">Pelanggan Umum</x-ui.button>
                    </div>

                    <div id="member-search-results" class="mt-3 hidden overflow-hidden rounded-lg border border-emerald-100 bg-white"></div>
                    <p class="mt-3 text-sm leading-6 text-emerald-800">
                        Jika pelanggan sudah terdaftar, histori transaksi akan tersimpan dan poin bertambah otomatis 1 poin setiap Rp 1.000.
                    </p>
                    <p id="member-points-hint" class="mt-2 text-sm font-semibold text-emerald-900"></p>
                </div>

                <div class="rounded-3xl border border-slate-100 bg-slate-50/70 p-4">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-end">
                        <div class="flex-1">
                            <label for="barcode-scan-input" class="field-label">Scan barcode / input SKU</label>
                            <input
                                id="barcode-scan-input"
                                type="text"
                                autocomplete="off"
                                placeholder="Arahkan scanner barcode ke sini, lalu scan"
                                class="field-input uppercase"
                            >
                        </div>

                        <x-ui.button type="button" variant="ghost" id="barcode-scan-submit">
                            Tambah dari Scan
                        </x-ui.button>
                    </div>

                    <p class="mt-3 text-sm leading-6 text-slate-500">
                        Scanner barcode retail biasanya terbaca seperti keyboard dan otomatis mengirim Enter setelah scan.
                    </p>
                </div>

                <div class="rounded-3xl border border-slate-100 bg-slate-50/70 p-4">
                    <div>
                        <label for="manual-search-input" class="field-label">Cari produk manual</label>
                        <input
                            id="manual-search-input"
                            type="text"
                            autocomplete="off"
                            placeholder="Ketik nama produk, SKU, barcode, atau lokasi penyimpanan"
                            class="field-input"
                        >
                    </div>

                    <div id="manual-search-results" class="mt-4 hidden space-y-2"></div>
                    <p id="manual-search-empty" class="mt-4 hidden rounded-2xl border border-dashed border-slate-200 bg-white px-4 py-3 text-sm text-slate-500">
                        Produk tidak ditemukan untuk kata kunci tersebut.
                    </p>
                </div>

                <div id="checkout-items" class="space-y-3">
                    @foreach ($oldItems as $index => $item)
                        <div class="checkout-row grid gap-3 rounded-3xl border border-slate-100 bg-slate-50/70 p-4 md:grid-cols-[1fr_140px_auto]">
                            <div>
                                <label class="field-label">Produk</label>
                                <select name="items[{{ $index }}][product_id]" class="field-select product-select" required>
                                    <option value="">Pilih produk</option>
                                    @foreach ($products as $product)
                                        <option
                                            value="{{ $product->id }}"
                                            data-price="{{ $product->sale_price }}"
                                            data-stock="{{ $product->stock }}"
                                            data-sku="{{ $product->sku }}"
                                            data-barcode="{{ $product->barcode }}"
                                            @selected(($item['product_id'] ?? '') == $product->id)
                                        >
                                            {{ $product->name }} - Rp {{ number_format($product->sale_price, 0, ',', '.') }} - stok {{ $product->stock }}{{ $product->storage_location ? ' - '.$product->storage_location : '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <x-ui.input
                                    label="Qty"
                                    :name="'items['.$index.'][quantity]'"
                                    type="number"
                                    min="1"
                                    :value="$item['quantity'] ?? 1"
                                    class="quantity-input"
                                    required
                                />
                            </div>

                            <div class="flex items-end">
                                <button type="button" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm font-semibold text-slate-600 transition hover:bg-slate-100 remove-row">
                                    Hapus
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>

                <template id="checkout-row-template">
                    <div class="checkout-row grid gap-3 rounded-3xl border border-slate-100 bg-slate-50/70 p-4 md:grid-cols-[1fr_140px_auto]">
                        <div>
                            <label class="field-label">Produk</label>
                            <select name="items[__INDEX__][product_id]" class="field-select product-select" required>
                                <option value="">Pilih produk</option>
                                @foreach ($products as $product)
                                    <option
                                        value="{{ $product->id }}"
                                        data-price="{{ $product->sale_price }}"
                                        data-stock="{{ $product->stock }}"
                                        data-sku="{{ $product->sku }}"
                                        data-barcode="{{ $product->barcode }}"
                                    >
                                        {{ $product->name }} - Rp {{ number_format($product->sale_price, 0, ',', '.') }} - stok {{ $product->stock }}{{ $product->storage_location ? ' - '.$product->storage_location : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="field-label">Qty</label>
                            <input name="items[__INDEX__][quantity]" type="number" min="1" value="1" class="field-input quantity-input" required>
                        </div>

                        <div class="flex items-end">
                            <button type="button" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm font-semibold text-slate-600 transition hover:bg-slate-100 remove-row">
                                Hapus
                            </button>
                        </div>
                    </div>
                </template>

                <div class="flex justify-between gap-3">
                    <button type="button" id="add-row" class="rounded-2xl border border-dashed border-slate-300 px-4 py-3 text-sm font-semibold text-slate-600 transition hover:border-slate-400 hover:bg-slate-50">
                        + Tambah Item
                    </button>
                    <div class="rounded-2xl bg-slate-950 px-4 py-3 text-right text-white">
                        <p class="text-xs uppercase tracking-[0.24em] text-white/60">Subtotal</p>
                        <p id="checkout-subtotal" class="mt-1 text-lg font-extrabold">Rp 0</p>
                    </div>
                </div>

                <div class="grid gap-4 md:grid-cols-3">
                    <div>
                        <x-ui.input label="Diskon" name="discount_amount" type="number" min="0" step="0.01" :value="old('discount_amount', 0)" class="summary-input" />
                    </div>
                    <div>
                        <x-ui.input label="Pajak/Biaya" name="tax_amount" type="number" min="0" step="0.01" :value="old('tax_amount', 0)" class="summary-input" />
                    </div>
                    <div>
                        <x-ui.select label="Metode pembayaran" name="payment_method" id="payment_method" required>
                            <option value="cash" @selected(old('payment_method', 'cash') === 'cash')>Cash</option>
                            <option value="qris" @selected(old('payment_method') === 'qris')>QRIS</option>
                            <option value="bank_transfer" @selected(old('payment_method') === 'bank_transfer')>Rekening</option>
                        </x-ui.select>
                    </div>
                </div>

                <div class="grid gap-4 rounded-lg border border-slate-200 bg-slate-50 p-4 md:grid-cols-2">
                    <div>
                        <x-ui.select label="Promo manual / event" name="promotion_id" id="promotion_id">
                            <option value="">Tanpa promo</option>
                            @foreach ($promotions as $promotion)
                                <option value="{{ $promotion->id }}" @selected(old('promotion_id') == $promotion->id)>
                                    {{ $promotion->name }} - {{ $promotion->label() }}{{ $promotion->member_only ? ' - khusus member' : '' }}
                                </option>
                            @endforeach
                        </x-ui.select>
                    </div>

                    <div>
                        <x-ui.select label="Tukar poin member" name="point_reward_id" id="point_reward_id">
                            <option value="">Tidak tukar poin</option>
                            @foreach ($pointRewards as $reward)
                                <option value="{{ $reward->id }}" @selected(old('point_reward_id') == $reward->id)>
                                    {{ $reward->name }} - {{ $reward->points_cost }} poin - potong Rp {{ number_format($reward->discount_amount, 0, ',', '.') }}
                                </option>
                            @endforeach
                        </x-ui.select>
                    </div>

                    <div class="md:col-span-2 grid gap-3 text-sm text-slate-600 md:grid-cols-3">
                        <div class="rounded-lg bg-white p-3">
                            <p class="text-xs uppercase tracking-[0.18em] text-slate-500">Diskon promo</p>
                            <p id="promo-discount-preview" class="mt-1 font-extrabold text-slate-950">Rp 0</p>
                        </div>
                        <div class="rounded-lg bg-white p-3">
                            <p class="text-xs uppercase tracking-[0.18em] text-slate-500">Tukar poin</p>
                            <p id="point-discount-preview" class="mt-1 font-extrabold text-slate-950">Rp 0</p>
                        </div>
                        <div class="rounded-lg bg-white p-3">
                            <p class="text-xs uppercase tracking-[0.18em] text-slate-500">Estimasi poin masuk</p>
                            <p id="points-earned-preview" class="mt-1 font-extrabold text-emerald-700">0 poin</p>
                        </div>
                    </div>
                </div>

                <div class="grid gap-4 md:grid-cols-1">
                    <div>
                        <x-ui.input label="Uang bayar / dana masuk" name="paid_amount" type="number" min="0" step="0.01" :value="old('paid_amount')" class="summary-input" required />
                    </div>
                </div>

                <div id="cash-payment-tools" class="rounded-3xl border border-slate-100 bg-slate-50/70 p-4">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                        <div>
                            <p class="text-sm font-semibold text-slate-900">Bantuan nominal pembayaran cash</p>
                            <p class="mt-1 text-sm leading-6 text-slate-500">
                                Untuk transaksi tunai, pilih uang pas atau klik nominal cepat agar kasir tidak perlu input manual terus-menerus.
                            </p>
                        </div>

                        <label class="inline-flex items-center gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700">
                            <input
                                type="checkbox"
                                id="is-exact-payment"
                                class="h-4 w-4 rounded border-emerald-300 text-emerald-600 focus:ring-emerald-200"
                            >
                            <span>Uang Pas</span>
                        </label>
                    </div>

                    <div class="mt-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.22em] text-slate-500">Nominal umum</p>
                        <div id="common-amounts" class="mt-3 flex flex-wrap gap-2">
                            @foreach ([2000, 5000, 10000, 20000, 50000, 100000] as $amount)
                                <button
                                    type="button"
                                    class="quick-amount rounded-2xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:border-slate-300 hover:bg-slate-100"
                                    data-amount="{{ $amount }}"
                                >
                                    Rp {{ number_format($amount, 0, ',', '.') }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <div class="mt-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.22em] text-slate-500">Nominal saran</p>
                        <div id="suggested-amounts" class="mt-3 flex flex-wrap gap-2"></div>
                    </div>
                </div>

                <div class="grid gap-4 rounded-3xl bg-slate-50 p-4 md:grid-cols-3">
                    <div>
                        <p class="text-xs uppercase tracking-[0.24em] text-slate-500">Grand Total</p>
                        <p id="checkout-total" class="mt-2 text-2xl font-extrabold text-slate-950">Rp 0</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-[0.24em] text-slate-500">Item</p>
                        <p id="checkout-count" class="mt-2 text-2xl font-extrabold text-slate-950">0</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-[0.24em] text-slate-500">Kembalian</p>
                        <p id="checkout-change" class="mt-2 text-2xl font-extrabold text-emerald-600">Rp 0</p>
                    </div>
                </div>

                <x-ui.button type="submit" variant="secondary" class="w-full">
                    Proses Checkout
                </x-ui.button>
            </form>
        </div>

        <div class="space-y-5">
            <div class="surface-card p-5">
                <h2 class="section-title">Katalog Cepat</h2>
                <p class="section-copy mt-1">Gunakan daftar ini sebagai referensi harga, stok, SKU, dan barcode.</p>

                <div class="mt-5 overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-3 py-2 font-semibold text-slate-600">Produk</th>
                                <th class="px-3 py-2 font-semibold text-slate-600">Kode</th>
                                <th class="px-3 py-2 font-semibold text-slate-600">Lokasi</th>
                                <th class="px-3 py-2 font-semibold text-slate-600">Stok</th>
                                <th class="px-3 py-2 font-semibold text-slate-600">Harga</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @foreach ($products as $product)
                                <tr>
                                    <td class="px-3 py-2 align-top">
                                        <p class="font-semibold text-slate-900">{{ $product->name }}</p>
                                        <p class="mt-1 text-slate-500">{{ $product->category->name }} - {{ $product->unit }}</p>
                                    </td>
                                    <td class="px-3 py-2 align-top text-slate-600">
                                        <p>{{ $product->sku }}</p>
                                        <p class="mt-1">{{ $product->barcode ?: '-' }}</p>
                                    </td>
                                    <td class="px-3 py-2 align-top text-slate-600">{{ $product->storage_location ?: '-' }}</td>
                                    <td class="px-3 py-2 align-top">
                                        <x-ui.badge :tone="$product->isLowStock() ? 'warning' : 'success'">{{ $product->stock }}</x-ui.badge>
                                    </td>
                                    <td class="px-3 py-2 align-top font-extrabold text-slate-950">Rp {{ number_format($product->sale_price, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="surface-card p-5">
                <h2 class="section-title">Penjualan Terbaru</h2>
                <p class="section-copy mt-1">Pantau transaksi terbaru yang sudah tersimpan.</p>

                <div class="mt-5 overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-3 py-2 font-semibold text-slate-600">Invoice</th>
                                <th class="px-3 py-2 font-semibold text-slate-600">Member</th>
                                <th class="px-3 py-2 font-semibold text-slate-600">Metode</th>
                                <th class="px-3 py-2 font-semibold text-slate-600">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @forelse ($recentSales as $sale)
                                <tr>
                                    <td class="px-3 py-2 align-top">
                                        <p class="font-semibold text-slate-900">{{ $sale->invoice_number }}</p>
                                        <p class="mt-1 text-slate-500">{{ $sale->sold_at->format('d M Y, H:i') }}</p>
                                    </td>
                                    <td class="px-3 py-2 align-top text-slate-600">{{ $sale->member?->name ?? 'Umum' }}</td>
                                    <td class="px-3 py-2 align-top">
                                        <x-ui.badge :tone="$sale->payment_method->badgeTone()">{{ $sale->payment_method->label() }}</x-ui.badge>
                                    </td>
                                    <td class="px-3 py-2 align-top font-extrabold text-slate-950">Rp {{ number_format($sale->grand_total, 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-3 py-6 text-center text-slate-500">Belum ada transaksi hari ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>

    @push('scripts')
        <script>
            (() => {
                const itemsContainer = document.getElementById('checkout-items');
                const template = document.getElementById('checkout-row-template');
                const addRowButton = document.getElementById('add-row');
                const subtotalEl = document.getElementById('checkout-subtotal');
                const totalEl = document.getElementById('checkout-total');
                const countEl = document.getElementById('checkout-count');
                const changeEl = document.getElementById('checkout-change');
                const paidAmountInput = document.querySelector('input[name="paid_amount"]');
                const memberInput = document.getElementById('member_id');
                const memberPhoneInput = document.getElementById('member-phone-input');
                const memberSearchResults = document.getElementById('member-search-results');
                const clearMemberSelection = document.getElementById('clear-member-selection');
                const memberPointsHint = document.getElementById('member-points-hint');
                const promotionInput = document.getElementById('promotion_id');
                const pointRewardInput = document.getElementById('point_reward_id');
                const promoDiscountPreview = document.getElementById('promo-discount-preview');
                const pointDiscountPreview = document.getElementById('point-discount-preview');
                const pointsEarnedPreview = document.getElementById('points-earned-preview');
                const paymentMethodInput = document.getElementById('payment_method');
                const exactPaymentCheckbox = document.getElementById('is-exact-payment');
                const suggestedAmounts = document.getElementById('suggested-amounts');
                const cashPaymentTools = document.getElementById('cash-payment-tools');
                const barcodeScanInput = document.getElementById('barcode-scan-input');
                const barcodeScanSubmit = document.getElementById('barcode-scan-submit');
                const manualSearchInput = document.getElementById('manual-search-input');
                const manualSearchResults = document.getElementById('manual-search-results');
                const manualSearchEmpty = document.getElementById('manual-search-empty');
                const productCatalog = @json($productCatalog);
                const memberCatalog = @json($memberCatalog);
                const promotionCatalog = @json($promotionCatalog);
                const rewardCatalog = @json($rewardCatalog);
                let index = {{ count($oldItems) }};

                const formatCurrency = (value) => `Rp ${new Intl.NumberFormat('id-ID').format(Math.max(0, value))}`;
                const roundUpTo = (amount, step) => Math.ceil(amount / step) * step;
                const normalizeCode = (value) => String(value || '').trim().toUpperCase();
                const normalizePhone = (value) => {
                    const digits = String(value || '').replace(/\D+/g, '');

                    if (digits.startsWith('62')) {
                        return `0${digits.slice(2)}`;
                    }

                    if (digits.startsWith('8')) {
                        return `0${digits}`;
                    }

                    return digits;
                };
                const getRows = () => Array.from(itemsContainer.querySelectorAll('.checkout-row'));
                const usesExactDigitalPayment = () => paymentMethodInput.value !== 'cash';

                const getCurrentTotal = () => {
                    const subtotal = Number(subtotalEl.dataset.rawSubtotal || 0);
                    const discount = Number(document.querySelector('input[name="discount_amount"]')?.value || 0);
                    const promoDiscount = Number(promoDiscountPreview?.dataset.amount || 0);
                    const pointDiscount = Number(pointDiscountPreview?.dataset.amount || 0);
                    const tax = Number(document.querySelector('input[name="tax_amount"]')?.value || 0);

                    return Math.max(0, subtotal - discount - promoDiscount - pointDiscount + tax);
                };

                const getSelectedMember = () => memberCatalog.find((member) => Number(member.id) === Number(memberInput.value));
                const getSelectedPromotion = () => promotionCatalog.find((promotion) => Number(promotion.id) === Number(promotionInput.value));
                const getSelectedReward = () => rewardCatalog.find((reward) => Number(reward.id) === Number(pointRewardInput.value));

                const syncSelectedMember = (member) => {
                    memberInput.value = member ? String(member.id) : '';
                    memberPhoneInput.value = member ? member.phone : '';
                    memberSearchResults.innerHTML = '';
                    memberSearchResults.classList.add('hidden');
                    recalculate();
                };

                const renderMemberSearchResults = () => {
                    const keyword = memberPhoneInput.value.trim();
                    const normalizedKeyword = normalizePhone(keyword);
                    const textKeyword = normalizeCode(keyword);

                    if (!keyword) {
                        syncSelectedMember(null);
                        return;
                    }

                    const exactMatch = memberCatalog.find((member) => normalizePhone(member.phone) === normalizedKeyword);

                    if (exactMatch) {
                        memberInput.value = String(exactMatch.id);
                        memberSearchResults.innerHTML = '';
                        memberSearchResults.classList.add('hidden');
                        recalculate();
                        return;
                    }

                    memberInput.value = '';

                    const matches = memberCatalog
                        .map((member) => {
                            const phone = normalizePhone(member.phone);
                            const name = normalizeCode(member.name);
                            const phoneStarts = normalizedKeyword && phone.startsWith(normalizedKeyword);
                            const phoneIncludes = normalizedKeyword && phone.includes(normalizedKeyword);
                            const nameIncludes = textKeyword && name.includes(textKeyword);
                            const score = phoneStarts ? 3 : phoneIncludes ? 2 : nameIncludes ? 1 : 0;

                            return { ...member, score };
                        })
                        .filter((member) => member.score > 0)
                        .sort((a, b) => b.score - a.score || a.name.localeCompare(b.name))
                        .slice(0, 8);

                    memberSearchResults.innerHTML = '';

                    if (matches.length === 0) {
                        const empty = document.createElement('div');
                        empty.className = 'px-4 py-3 text-sm text-slate-500';
                        empty.textContent = 'Member tidak ditemukan. Transaksi akan dianggap pelanggan umum.';
                        memberSearchResults.appendChild(empty);
                        memberSearchResults.classList.remove('hidden');
                        recalculate();
                        return;
                    }

                    matches.forEach((member) => {
                        const button = document.createElement('button');
                        button.type = 'button';
                        button.className = 'flex w-full items-center justify-between gap-3 border-b border-slate-100 px-4 py-3 text-left transition last:border-b-0 hover:bg-emerald-50';

                        const label = document.createElement('span');
                        label.className = 'min-w-0';

                        const name = document.createElement('span');
                        name.className = 'block truncate text-sm font-semibold text-slate-900';
                        name.textContent = member.name;

                        const phone = document.createElement('span');
                        phone.className = 'mt-1 block truncate text-xs text-slate-500';
                        phone.textContent = member.phone;

                        const points = document.createElement('span');
                        points.className = 'shrink-0 rounded-lg bg-emerald-50 px-3 py-2 text-xs font-semibold text-emerald-700';
                        points.textContent = `${member.points} poin`;

                        label.append(name, phone);
                        button.append(label, points);
                        button.addEventListener('click', () => syncSelectedMember(member));
                        memberSearchResults.appendChild(button);
                    });

                    memberSearchResults.classList.remove('hidden');
                    recalculate();
                };

                const syncPaymentMethodState = () => {
                    const isCash = !usesExactDigitalPayment();

                    paidAmountInput.readOnly = !isCash || exactPaymentCheckbox.checked;
                    paidAmountInput.classList.toggle('bg-slate-100', !isCash);
                    paidAmountInput.classList.toggle('cursor-not-allowed', !isCash);
                    exactPaymentCheckbox.disabled = !isCash;
                    cashPaymentTools.classList.toggle('opacity-60', !isCash);

                    if (!isCash) {
                        exactPaymentCheckbox.checked = false;
                        paidAmountInput.value = getCurrentTotal();
                    }
                };

                const setPaidAmount = (amount) => {
                    paidAmountInput.value = amount > 0 ? amount : 0;
                    exactPaymentCheckbox.checked = !usesExactDigitalPayment() && Number(amount) === getCurrentTotal();
                    paidAmountInput.readOnly = exactPaymentCheckbox.checked || usesExactDigitalPayment();
                    recalculate();
                };

                const renderSuggestedAmounts = (total) => {
                    suggestedAmounts.innerHTML = '';

                    [
                        { label: 'Uang Pas', amount: total },
                        { label: 'Bulat 1rb', amount: roundUpTo(total || 1000, 1000) },
                        { label: 'Bulat 5rb', amount: roundUpTo(total || 5000, 5000) },
                        { label: 'Bulat 10rb', amount: roundUpTo(total || 10000, 10000) },
                        { label: 'Bulat 20rb', amount: roundUpTo(total || 20000, 20000) },
                    ].forEach(({ label, amount }) => {
                        const button = document.createElement('button');
                        button.type = 'button';
                        button.className = 'quick-amount rounded-2xl border border-amber-200 bg-amber-50 px-4 py-2 text-sm font-semibold text-amber-700 transition hover:border-amber-300 hover:bg-amber-100';
                        button.dataset.amount = amount;
                        button.textContent = `${label} - ${formatCurrency(amount)}`;
                        button.addEventListener('click', () => setPaidAmount(amount));
                        suggestedAmounts.appendChild(button);
                    });
                };

                const bindRowEvents = (row) => {
                    row.querySelector('.remove-row')?.addEventListener('click', () => {
                        if (itemsContainer.children.length === 1) {
                            row.querySelector('.product-select').value = '';
                            row.querySelector('.quantity-input').value = 1;
                        } else {
                            row.remove();
                        }

                        recalculate();
                    });

                    row.querySelector('.product-select')?.addEventListener('change', recalculate);
                    row.querySelector('.quantity-input')?.addEventListener('input', recalculate);
                };

                const createRow = () => {
                    const html = template.innerHTML.replaceAll('__INDEX__', index++);
                    itemsContainer.insertAdjacentHTML('beforeend', html);
                    const row = itemsContainer.lastElementChild;
                    bindRowEvents(row);

                    return row;
                };

                const findAvailableRow = () => {
                    return getRows().find((row) => !row.querySelector('.product-select')?.value);
                };

                const findRowByProductId = (productId) => {
                    return getRows().find((row) => Number(row.querySelector('.product-select')?.value || 0) === Number(productId));
                };

                const findProductByCode = (code) => {
                    const normalizedCode = normalizeCode(code);

                    return productCatalog.find((product) => {
                        return normalizeCode(product.barcode) === normalizedCode || normalizeCode(product.sku) === normalizedCode;
                    });
                };

                const addProductToCheckout = (product) => {
                    let row = findRowByProductId(product.id);

                    if (row) {
                        const quantityInput = row.querySelector('.quantity-input');
                        quantityInput.value = Number(quantityInput.value || 0) + 1;
                        recalculate();
                        return;
                    }

                    row = findAvailableRow() || createRow();
                    row.querySelector('.product-select').value = String(product.id);
                    row.querySelector('.quantity-input').value = 1;
                    recalculate();
                };

                const processScanInput = () => {
                    const scannedCode = normalizeCode(barcodeScanInput.value);

                    if (!scannedCode) {
                        barcodeScanInput.focus();
                        return;
                    }

                    const product = findProductByCode(scannedCode);

                    if (!product) {
                        barcodeScanInput.select();
                        window.alert(`Barcode atau SKU "${scannedCode}" tidak ditemukan.`);
                        return;
                    }

                    addProductToCheckout(product);
                    barcodeScanInput.value = '';
                    barcodeScanInput.focus();
                };

                const clearManualSearch = ({ keepValue = false } = {}) => {
                    if (!keepValue) {
                        manualSearchInput.value = '';
                    }

                    manualSearchResults.innerHTML = '';
                    manualSearchResults.classList.add('hidden');
                    manualSearchEmpty.classList.add('hidden');
                };

                const renderManualSearchResults = (query) => {
                    const keyword = normalizeCode(query);

                    if (!keyword) {
                        clearManualSearch({ keepValue: true });
                        return;
                    }

                    const matches = productCatalog
                        .filter((product) => {
                            return [
                                product.name,
                                product.sku,
                                product.barcode,
                                product.category,
                                product.location,
                            ].some((value) => normalizeCode(value).includes(keyword));
                        })
                        .slice(0, 8);

                    manualSearchResults.innerHTML = '';

                    if (matches.length === 0) {
                        manualSearchResults.classList.add('hidden');
                        manualSearchEmpty.classList.remove('hidden');
                        return;
                    }

                    manualSearchEmpty.classList.add('hidden');
                    manualSearchResults.classList.remove('hidden');

                    matches.forEach((product) => {
                        const button = document.createElement('button');
                        button.type = 'button';
                        button.className = 'flex w-full items-center justify-between gap-3 rounded-2xl border border-slate-200 bg-white px-4 py-3 text-left transition hover:border-slate-300 hover:bg-slate-50';
                        button.innerHTML = `
                            <span class="min-w-0">
                                <span class="block truncate text-sm font-semibold text-slate-900">${product.name}</span>
                                <span class="mt-1 block truncate text-xs text-slate-500">
                                    ${product.category} - SKU ${product.sku}${product.barcode ? ` - barcode ${product.barcode}` : ''}${product.location ? ` - ${product.location}` : ''}
                                </span>
                            </span>
                            <span class="shrink-0 text-right">
                                <span class="block text-sm font-extrabold text-slate-950">${formatCurrency(product.price)}</span>
                                <span class="mt-1 block text-xs text-slate-500">stok ${product.stock}</span>
                            </span>
                        `;
                        button.addEventListener('click', () => {
                            addProductToCheckout(product);
                            clearManualSearch();
                            manualSearchInput.focus();
                        });
                        manualSearchResults.appendChild(button);
                    });
                };

                const recalculate = () => {
                    let subtotal = 0;
                    let count = 0;

                    getRows().forEach((row) => {
                        const product = row.querySelector('.product-select');
                        const selected = product?.selectedOptions?.[0];
                        const price = Number(selected?.dataset?.price || 0);
                        const quantity = Number(row.querySelector('.quantity-input')?.value || 0);

                        subtotal += price * quantity;
                        count += quantity;
                    });

                    const discount = Number(document.querySelector('input[name="discount_amount"]')?.value || 0);
                    const tax = Number(document.querySelector('input[name="tax_amount"]')?.value || 0);
                    const member = getSelectedMember();
                    const promotion = getSelectedPromotion();
                    const reward = getSelectedReward();
                    const baseAfterManualDiscount = Math.max(0, subtotal - discount);
                    let promoDiscount = 0;
                    let pointDiscount = 0;

                    if (promotion) {
                        if (promotion.memberOnly && !member) {
                            promoDiscountPreview.textContent = 'Pilih member';
                            promoDiscountPreview.dataset.amount = 0;
                        } else if (promotion.type === 'fixed') {
                            promoDiscount = Math.min(baseAfterManualDiscount, promotion.value);
                        } else {
                            promoDiscount = Math.min(baseAfterManualDiscount, baseAfterManualDiscount * (promotion.value / 100));
                        }
                    }

                    if (reward) {
                        if (!member) {
                            pointDiscountPreview.textContent = 'Pilih member';
                            pointDiscountPreview.dataset.amount = 0;
                        } else if (member.points < reward.points) {
                            pointDiscountPreview.textContent = `Kurang ${reward.points - member.points} poin`;
                            pointDiscountPreview.dataset.amount = 0;
                        } else {
                            pointDiscount = Math.min(reward.discount, Math.max(0, baseAfterManualDiscount - promoDiscount));
                        }
                    }

                    const total = Math.max(0, subtotal - discount - promoDiscount - pointDiscount + tax);
                    const pointsEarned = member ? Math.floor(total / 1000) : 0;

                    subtotalEl.dataset.rawSubtotal = subtotal;
                    promoDiscountPreview.dataset.amount = promoDiscount;
                    pointDiscountPreview.dataset.amount = pointDiscount;

                    if (!promotion || !promotion.memberOnly || member) {
                        promoDiscountPreview.textContent = formatCurrency(promoDiscount);
                    }

                    if (!reward || (member && member.points >= reward.points)) {
                        pointDiscountPreview.textContent = formatCurrency(pointDiscount);
                    }

                    pointsEarnedPreview.textContent = `${pointsEarned} poin`;
                    memberPointsHint.textContent = member
                        ? `${member.name} punya ${member.points} poin saat ini.`
                        : 'Pilih member untuk memakai dan mengumpulkan poin.';

                    if (usesExactDigitalPayment()) {
                        paidAmountInput.value = total;
                    } else if (exactPaymentCheckbox.checked) {
                        paidAmountInput.value = total;
                    }

                    const paid = Number(paidAmountInput?.value || 0);
                    const change = Math.max(0, paid - total);

                    subtotalEl.textContent = formatCurrency(subtotal);
                    totalEl.textContent = formatCurrency(total);
                    changeEl.textContent = formatCurrency(change);
                    countEl.textContent = count;
                    renderSuggestedAmounts(total);
                };

                addRowButton.addEventListener('click', () => {
                    createRow();
                    recalculate();
                });

                getRows().forEach(bindRowEvents);

                document.querySelectorAll('.summary-input').forEach((input) => {
                    input.addEventListener('input', () => {
                        if (input === paidAmountInput && exactPaymentCheckbox.checked) {
                            exactPaymentCheckbox.checked = false;
                            paidAmountInput.readOnly = false;
                        }

                        recalculate();
                    });
                });

                paymentMethodInput.addEventListener('change', () => {
                    syncPaymentMethodState();
                    recalculate();
                });

                memberPhoneInput.addEventListener('input', renderMemberSearchResults);
                memberPhoneInput.addEventListener('keydown', (event) => {
                    if (event.key !== 'Enter') {
                        return;
                    }

                    const firstResult = memberSearchResults.querySelector('button');

                    if (!firstResult) {
                        return;
                    }

                    event.preventDefault();
                    firstResult.click();
                });

                clearMemberSelection.addEventListener('click', () => syncSelectedMember(null));

                [promotionInput, pointRewardInput].forEach((input) => {
                    input.addEventListener('change', recalculate);
                });

                document.querySelectorAll('#common-amounts .quick-amount').forEach((button) => {
                    button.addEventListener('click', () => setPaidAmount(Number(button.dataset.amount || 0)));
                });

                exactPaymentCheckbox.addEventListener('change', () => {
                    paidAmountInput.readOnly = exactPaymentCheckbox.checked;

                    if (exactPaymentCheckbox.checked) {
                        setPaidAmount(getCurrentTotal());
                        return;
                    }

                    recalculate();
                });

                barcodeScanInput.addEventListener('keydown', (event) => {
                    if (event.key !== 'Enter') {
                        return;
                    }

                    event.preventDefault();
                    processScanInput();
                });

                barcodeScanSubmit.addEventListener('click', processScanInput);
                manualSearchInput.addEventListener('input', () => renderManualSearchResults(manualSearchInput.value));
                manualSearchInput.addEventListener('keydown', (event) => {
                    if (event.key !== 'Enter') {
                        return;
                    }

                    event.preventDefault();

                    const firstResult = manualSearchResults.querySelector('button');

                    if (firstResult) {
                        firstResult.click();
                    }
                });

                syncPaymentMethodState();
                recalculate();
                barcodeScanInput.focus();
            })();
        </script>
    @endpush
</x-layouts.app>
