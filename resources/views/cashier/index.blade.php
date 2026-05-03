@php
    $oldItems = old('items', [['product_id' => '', 'quantity' => 1]]);
    $productCatalog = $products->map(fn ($product) => [
        'id' => $product->id,
        'name' => $product->name,
        'sku' => $product->sku,
        'barcode' => $product->barcode,
        'category' => $product->category->name,
        'unit' => $product->unit,
        'price' => (float) $product->sale_price,
        'stock' => $product->stock,
    ])->values();
@endphp

<x-layouts.app
    title="Kasir"
    heading="Meja Kasir"
    description="Rangkai transaksi cepat, hitung total belanja, dan catat dana masuk sesuai metode pembayaran."
>
    <section class="grid gap-5 xl:grid-cols-[1fr_0.9fr]">
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
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-end">
                        <div class="flex-1">
                            <label for="manual-search-input" class="field-label">Cari produk manual</label>
                            <input
                                id="manual-search-input"
                                type="text"
                                autocomplete="off"
                                placeholder="Ketik nama produk, SKU, atau barcode"
                                class="field-input"
                            >
                        </div>

                        <div class="rounded-2xl bg-white px-4 py-3 text-sm text-slate-500">
                            Cocok untuk toko dengan katalog barang yang sudah banyak.
                        </div>
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
                                            {{ $product->name }} - Rp {{ number_format($product->sale_price, 0, ',', '.') }} - stok {{ $product->stock }}
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
                                        {{ $product->name }} - Rp {{ number_format($product->sale_price, 0, ',', '.') }} - stok {{ $product->stock }}
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

                <div class="mt-5 grid gap-3">
                    @foreach ($products as $product)
                        <div class="rounded-3xl border border-slate-100 p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="font-semibold text-slate-900">{{ $product->name }}</p>
                                    <p class="mt-1 text-sm text-slate-500">
                                        {{ $product->category->name }} - {{ $product->unit }} - SKU {{ $product->sku }}
                                    </p>
                                    @if ($product->barcode)
                                        <p class="mt-1 text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">
                                            Barcode {{ $product->barcode }}
                                        </p>
                                    @endif
                                </div>
                                <x-ui.badge :tone="$product->isLowStock() ? 'warning' : 'success'">
                                    stok {{ $product->stock }}
                                </x-ui.badge>
                            </div>
                            <p class="mt-3 text-lg font-extrabold text-slate-950">Rp {{ number_format($product->sale_price, 0, ',', '.') }}</p>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="surface-card p-5">
                <h2 class="section-title">Penjualan Terbaru</h2>
                <p class="section-copy mt-1">Pantau transaksi terbaru yang sudah tersimpan.</p>

                <div class="mt-5 space-y-3">
                    @forelse ($recentSales as $sale)
                        <div class="rounded-3xl border border-slate-100 bg-slate-50/60 p-4">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <p class="font-semibold text-slate-900">{{ $sale->invoice_number }}</p>
                                    <p class="mt-1 text-sm text-slate-500">{{ $sale->sold_at->format('d M Y, H:i') }}</p>
                                </div>
                                <div class="text-right">
                                    <div class="mb-2">
                                        <x-ui.badge :tone="$sale->payment_method->badgeTone()">{{ $sale->payment_method->label() }}</x-ui.badge>
                                    </div>
                                    <p class="text-sm text-slate-500">{{ $sale->cashier->name }}</p>
                                    <p class="text-lg font-extrabold text-slate-950">Rp {{ number_format($sale->grand_total, 0, ',', '.') }}</p>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="rounded-3xl border border-dashed border-slate-200 bg-slate-50 p-6 text-sm text-slate-500">
                            Belum ada transaksi hari ini.
                        </div>
                    @endforelse
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
                let index = {{ count($oldItems) }};

                const formatCurrency = (value) => `Rp ${new Intl.NumberFormat('id-ID').format(Math.max(0, value))}`;
                const roundUpTo = (amount, step) => Math.ceil(amount / step) * step;
                const normalizeCode = (value) => String(value || '').trim().toUpperCase();
                const getRows = () => Array.from(itemsContainer.querySelectorAll('.checkout-row'));
                const usesExactDigitalPayment = () => paymentMethodInput.value !== 'cash';

                const getCurrentTotal = () => {
                    const subtotal = Number(subtotalEl.dataset.rawSubtotal || 0);
                    const discount = Number(document.querySelector('input[name="discount_amount"]')?.value || 0);
                    const tax = Number(document.querySelector('input[name="tax_amount"]')?.value || 0);

                    return Math.max(0, subtotal - discount + tax);
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
                                    ${product.category} - SKU ${product.sku}${product.barcode ? ` - barcode ${product.barcode}` : ''}
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
                    const total = Math.max(0, subtotal - discount + tax);

                    subtotalEl.dataset.rawSubtotal = subtotal;

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
