<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Label Barcode {{ $product->name }} | {{ $appSettings['app_name'] ?? config('app.name') }}</title>
    @vite(['resources/css/app.css'])
    <style>
        .barcode-figure {
            overflow: hidden;
        }

        .barcode-svg {
            display: block;
            width: 100%;
            max-width: 100%;
            height: 78px;
            margin: 0 auto;
        }

        @media print {
            .no-print {
                display: none !important;
            }

            body {
                background: #fff !important;
            }

            .label-sheet {
                gap: 8mm !important;
            }

            .barcode-label {
                break-inside: avoid;
                box-shadow: none !important;
                border-color: #111827 !important;
            }
        }
    </style>
</head>
<body class="px-4 py-6 sm:px-6">
    <div class="mx-auto max-w-5xl space-y-5">
        <div class="no-print surface-card flex flex-col gap-4 p-5 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.28em] text-slate-500">Label Barcode Produk</p>
                <h1 class="mt-2 text-2xl font-extrabold text-slate-950">{{ $product->name }}</h1>
                <p class="mt-2 text-sm text-slate-500">
                    Barcode {{ $product->barcode }} - SKU {{ $product->sku }}
                </p>
            </div>

            <div class="flex flex-wrap gap-3">
                <form method="GET" action="{{ route('stock.products.label', $product) }}" class="flex items-end gap-3">
                    <div>
                        <label for="copies" class="field-label">Jumlah label</label>
                        <input id="copies" name="copies" type="number" min="1" max="48" value="{{ $copies }}" class="field-input w-28">
                    </div>
                    <x-ui.button type="submit" variant="ghost">Perbarui</x-ui.button>
                </form>

                <a href="{{ route('stock.products.index') }}">
                    <x-ui.button variant="ghost">Kembali ke Produk</x-ui.button>
                </a>

                <x-ui.button type="button" variant="secondary" onclick="window.print()">
                    Cetak Label
                </x-ui.button>
            </div>
        </div>

        <section class="label-sheet grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @for ($i = 0; $i < $copies; $i++)
                <article class="barcode-label rounded-[28px] border border-slate-300 bg-white p-5 shadow-[0_20px_60px_-40px_rgba(15,23,42,0.35)]">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-[11px] font-semibold uppercase tracking-[0.28em] text-slate-500">Stiker Produk</p>
                            <h2 class="mt-2 text-lg font-extrabold text-slate-950">{{ $product->name }}</h2>
                            <p class="mt-1 text-sm text-slate-500">{{ $product->category->name }} - {{ $product->unit }}</p>
                        </div>
                        <div class="rounded-2xl bg-slate-950 px-3 py-2 text-right text-white">
                            <p class="text-[10px] uppercase tracking-[0.22em] text-white/60">Harga</p>
                            <p class="mt-1 text-base font-extrabold">Rp {{ number_format($product->sale_price, 0, ',', '.') }}</p>
                        </div>
                    </div>

                    <div class="barcode-figure mt-5 rounded-2xl border border-slate-200 bg-slate-50 px-3 py-4">
                        {!! $barcodeSvg !!}
                    </div>

                    <div class="mt-4 flex items-center justify-between gap-3 text-sm">
                        <div>
                            <p class="text-xs uppercase tracking-[0.22em] text-slate-500">Barcode</p>
                            <p class="mt-1 font-semibold text-slate-950">{{ $product->barcode }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-xs uppercase tracking-[0.22em] text-slate-500">SKU</p>
                            <p class="mt-1 font-semibold text-slate-950">{{ $product->sku }}</p>
                        </div>
                    </div>
                </article>
            @endfor
        </section>
    </div>
</body>
</html>
