<?php

namespace App\Http\Controllers;

use App\Enums\PaymentMethod;
use App\Models\Sale;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SalesReportController extends Controller
{
    public function index(Request $request): View
    {
        [$startDate, $endDate, $paymentMethod, $period] = $this->filters($request);
        [$periodLabel, $rangeLabel] = $this->resolvePeriodMeta($startDate, $endDate, $period);

        $sales = $this->baseQuery($startDate, $endDate, $paymentMethod)
            ->with(['cashier', 'items'])
            ->latest('sold_at')
            ->paginate(15)
            ->withQueryString();

        $summary = collect(PaymentMethod::cases())->map(function (PaymentMethod $method) use ($startDate, $endDate) {
            $aggregate = $this->baseQuery($startDate, $endDate, $method->value)
                ->selectRaw('COUNT(*) as transactions, COALESCE(SUM(grand_total), 0) as total_amount')
                ->first();

            return [
                'value' => $method->value,
                'label' => $method->label(),
                'tone' => $method->badgeTone(),
                'transactions' => (int) ($aggregate->transactions ?? 0),
                'total_amount' => (float) ($aggregate->total_amount ?? 0),
            ];
        });

        $reportTotals = $this->baseQuery($startDate, $endDate, $paymentMethod)
            ->selectRaw('COUNT(*) as transactions, COALESCE(SUM(grand_total), 0) as total_amount, COALESCE(SUM(change_amount), 0) as total_change')
            ->first();

        $trend = $this->buildTrendData($startDate, $endDate, $period, $paymentMethod);

        return view('reports.sales', [
            'sales' => $sales,
            'summary' => $summary,
            'selectedStartDate' => $startDate,
            'selectedEndDate' => $endDate,
            'selectedPaymentMethod' => $paymentMethod,
            'selectedPeriod' => $period,
            'periodLabel' => $periodLabel,
            'rangeLabel' => $rangeLabel,
            'reportTotals' => $reportTotals,
            'trend' => $trend,
            'paymentMethods' => PaymentMethod::cases(),
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        [$startDate, $endDate, $paymentMethod, $period] = $this->filters($request);
        [$periodLabel, $rangeLabel] = $this->resolvePeriodMeta($startDate, $endDate, $period);

        $sales = $this->baseQuery($startDate, $endDate, $paymentMethod)
            ->with(['cashier', 'items'])
            ->latest('sold_at')
            ->get();

        $summary = collect(PaymentMethod::cases())->map(function (PaymentMethod $method) use ($startDate, $endDate) {
            $aggregate = $this->baseQuery($startDate, $endDate, $method->value)
                ->selectRaw('COUNT(*) as transactions, COALESCE(SUM(grand_total), 0) as total_amount')
                ->first();

            return [
                $method->label(),
                (int) ($aggregate->transactions ?? 0),
                (float) ($aggregate->total_amount ?? 0),
            ];
        });

        $fileName = 'laporan-penjualan-'.$period.'-'.$startDate.($paymentMethod ? '-'.$paymentMethod : '').'.csv';

        return response()->streamDownload(function () use ($sales, $summary, $paymentMethod, $periodLabel, $rangeLabel): void {
            $handle = fopen('php://output', 'w');

            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($handle, ['Laporan Penjualan '.$periodLabel]);
            fputcsv($handle, ['Periode', $rangeLabel]);
            fputcsv($handle, ['Filter Metode Pembayaran', $paymentMethod ? PaymentMethod::from($paymentMethod)->label() : 'Semua']);
            fputcsv($handle, []);
            fputcsv($handle, ['Rekap Per Metode']);
            fputcsv($handle, ['Metode', 'Jumlah Transaksi', 'Total Dana Masuk']);

            foreach ($summary as $row) {
                fputcsv($handle, $row);
            }

            fputcsv($handle, []);
            fputcsv($handle, ['Detail Transaksi']);
            fputcsv($handle, ['Invoice', 'Tanggal', 'Kasir', 'Metode', 'Total Item', 'Subtotal', 'Diskon', 'Pajak/Biaya', 'Grand Total', 'Dana Masuk', 'Kembalian']);

            foreach ($sales as $sale) {
                fputcsv($handle, [
                    $sale->invoice_number,
                    $sale->sold_at->format('Y-m-d H:i'),
                    $sale->cashier->name,
                    $sale->payment_method->label(),
                    $sale->total_items,
                    $sale->subtotal,
                    $sale->discount_amount,
                    $sale->tax_amount,
                    $sale->grand_total,
                    $sale->paid_amount,
                    $sale->change_amount,
                ]);
            }

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function filters(Request $request): array
    {
        $period = $request->string('period')->toString() ?: 'daily';

        if (! in_array($period, ['daily', 'weekly', 'monthly', 'yearly'], true)) {
            $period = 'daily';
        }

        [$defaultStartDate, $defaultEndDate] = $this->defaultRangeForPeriod(
            $period,
            $request->string('date')->toString()
        );

        $startDate = $this->parseDateInput($request->string('start_date')->toString(), $defaultStartDate);
        $endDate = $this->parseDateInput($request->string('end_date')->toString(), $defaultEndDate);

        $paymentMethod = $request->string('payment_method')->toString() ?: null;

        if ($paymentMethod && ! PaymentMethod::tryFrom($paymentMethod)) {
            $paymentMethod = null;
        }

        if (Carbon::parse($startDate)->gt(Carbon::parse($endDate))) {
            [$startDate, $endDate] = [$endDate, $startDate];
        }

        return [$startDate, $endDate, $paymentMethod, $period];
    }

    private function baseQuery(string $startDate, string $endDate, ?string $paymentMethod = null): Builder
    {
        return Sale::query()
            ->whereBetween('sold_at', [
                Carbon::parse($startDate)->startOfDay(),
                Carbon::parse($endDate)->endOfDay(),
            ])
            ->when($paymentMethod, fn (Builder $query) => $query->where('payment_method', $paymentMethod));
    }

    private function defaultRangeForPeriod(string $period, string $legacyDateInput = ''): array
    {
        $baseDate = $this->parseBaseDate($legacyDateInput);

        return match ($period) {
            'weekly' => [
                $baseDate->copy()->startOfWeek()->toDateString(),
                $baseDate->copy()->endOfWeek()->toDateString(),
            ],
            'monthly' => [
                $baseDate->copy()->startOfMonth()->toDateString(),
                $baseDate->copy()->endOfMonth()->toDateString(),
            ],
            'yearly' => [
                $baseDate->copy()->startOfYear()->toDateString(),
                $baseDate->copy()->endOfYear()->toDateString(),
            ],
            default => [
                $baseDate->toDateString(),
                $baseDate->toDateString(),
            ],
        };
    }

    private function resolvePeriodMeta(string $startDate, string $endDate, string $period): array
    {
        $periodLabel = match ($period) {
            'weekly' => 'Mingguan',
            'monthly' => 'Bulanan',
            'yearly' => 'Tahunan',
            default => 'Harian',
        };

        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        $rangeLabel = $start->isSameDay($end)
            ? $start->format('d M Y')
            : $start->format('d M Y').' - '.$end->format('d M Y');

        return [$periodLabel, $rangeLabel];
    }

    private function buildTrendData(string $startDate, string $endDate, string $period, ?string $paymentMethod): array
    {
        $sales = $this->baseQuery($startDate, $endDate, $paymentMethod)
            ->get(['sold_at', 'grand_total']);

        $groups = match ($period) {
            'yearly' => $this->buildYearlyTrend($sales, $startDate, $endDate),
            'monthly' => $this->buildMonthlyTrend($sales, $startDate, $endDate),
            'weekly' => $this->buildWeeklyTrend($sales, $startDate, $endDate),
            default => $this->buildDailyTrend($sales, $startDate, $endDate),
        };

        $maxAmount = max(1, collect($groups)->max('amount'));

        return collect($groups)->map(fn (array $row) => [
            ...$row,
            'height' => max(10, (int) round(($row['amount'] / $maxAmount) * 100)),
        ])->all();
    }

    private function buildDailyTrend($sales, string $startDate, string $endDate): array
    {
        if (! Carbon::parse($startDate)->isSameDay($endDate)) {
            $grouped = $sales->groupBy(fn ($sale) => $sale->sold_at->toDateString());

            return collect(CarbonPeriod::create($startDate, $endDate))
                ->map(function ($date) use ($grouped): array {
                    return [
                        'label' => $date->format('d M'),
                        'amount' => (float) ($grouped->get($date->toDateString())?->sum('grand_total') ?? 0),
                    ];
                })
                ->all();
        }

        $grouped = $sales->groupBy(fn ($sale) => $sale->sold_at->format('H'));

        return collect(range(0, 23))
            ->map(function (int $hour) use ($grouped): array {
                $key = str_pad((string) $hour, 2, '0', STR_PAD_LEFT);

                return [
                    'label' => $key,
                    'amount' => (float) ($grouped->get($key)?->sum('grand_total') ?? 0),
                ];
            })
            ->all();
    }

    private function buildWeeklyTrend($sales, string $startDate, string $endDate): array
    {
        $grouped = $sales->groupBy(fn ($sale) => $sale->sold_at->toDateString());

        return collect(CarbonPeriod::create($startDate, $endDate))
            ->map(function ($date) use ($grouped): array {
                return [
                    'label' => $date->format('d M'),
                    'amount' => (float) ($grouped->get($date->toDateString())?->sum('grand_total') ?? 0),
                ];
            })
            ->all();
    }

    private function buildMonthlyTrend($sales, string $startDate, string $endDate): array
    {
        $grouped = $sales->groupBy(fn ($sale) => $sale->sold_at->toDateString());

        return collect(CarbonPeriod::create($startDate, $endDate))
            ->map(function ($date) use ($grouped): array {
                return [
                    'label' => $date->format('d M'),
                    'amount' => (float) ($grouped->get($date->toDateString())?->sum('grand_total') ?? 0),
                ];
            })
            ->all();
    }

    private function buildYearlyTrend($sales, string $startDate, string $endDate): array
    {
        $grouped = $sales->groupBy(fn ($sale) => $sale->sold_at->format('Y-m'));
        $period = CarbonPeriod::create(
            Carbon::parse($startDate)->startOfMonth(),
            '1 month',
            Carbon::parse($endDate)->startOfMonth()
        );

        return collect($period)
            ->map(function ($date) use ($grouped): array {
                return [
                    'label' => $date->translatedFormat('M Y'),
                    'amount' => (float) ($grouped->get($date->format('Y-m'))?->sum('grand_total') ?? 0),
                ];
            })
            ->all();
    }

    private function parseBaseDate(string $value): Carbon
    {
        if ($value !== '') {
            try {
                return Carbon::parse($value);
            } catch (\Throwable) {
                // Fallback to current date when legacy input is invalid.
            }
        }

        return now();
    }

    private function parseDateInput(string $value, string $default): string
    {
        if ($value === '') {
            return $default;
        }

        try {
            return Carbon::parse($value)->toDateString();
        } catch (\Throwable) {
            return $default;
        }
    }
}
