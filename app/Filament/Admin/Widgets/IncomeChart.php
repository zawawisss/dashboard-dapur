<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Transaction;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Schema;

class IncomeChart extends ChartWidget
{
    protected ?string $heading = 'Tren Pemasukan (1 Tahun Terakhir)';
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 1;

    protected function getData(): array
    {
        if (! Schema::hasTable('transactions') || ! Schema::hasTable('categories')) {
            return [
                'datasets' => [[
                    'label' => 'Total Pemasukan (Rp)',
                    'data' => array_fill(0, 12, 0),
                    'borderColor' => '#10b981',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.2)',
                    'fill' => true,
                ]],
                'labels' => collect(range(11, 0))
                    ->map(fn ($i) => Carbon::now()->subMonths($i)->translatedFormat('M Y'))
                    ->all(),
            ];
        }

        $startDate = Carbon::now()->subMonths(11)->startOfMonth();
        $endDate   = Carbon::now()->endOfMonth();

        $driver = \Illuminate\Support\Facades\DB::getDriverName();
        $selectRaw = $driver === 'sqlite'
            ? "strftime('%Y', transactions.date) as year, strftime('%m', transactions.date) as month, SUM(transactions.amount) as total"
            : "YEAR(transactions.date) as year, MONTH(transactions.date) as month, SUM(transactions.amount) as total";
        $groupByRaw = $driver === 'sqlite'
            ? "strftime('%Y', transactions.date), strftime('%m', transactions.date)"
            : "YEAR(transactions.date), MONTH(transactions.date)";

        $results = Transaction::query()
            ->join('categories', 'transactions.category_id', '=', 'categories.id')
            ->where('categories.type', 'IN')
            ->whereBetween('transactions.date', [$startDate, $endDate])
            ->selectRaw($selectRaw)
            ->groupByRaw($groupByRaw)
            ->get()
            ->keyBy(fn($r) => $r->year . '-' . (int)$r->month);

        $data   = [];
        $labels = [];

        for ($i = 11; $i >= 0; $i--) {
            $month    = Carbon::now()->subMonths($i)->startOfMonth();
            $key      = $month->year . '-' . $month->month;
            $data[]   = $results->get($key)?->total ?? 0;
            $labels[] = $month->translatedFormat('M Y');
        }

        return [
            'datasets' => [[
                'label'           => 'Total Pemasukan (Rp)',
                'data'            => $data,
                'borderColor'     => '#10b981',
                'backgroundColor' => 'rgba(16, 185, 129, 0.2)',
                'fill'            => true,
            ]],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
