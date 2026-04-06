<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Transaction;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Schema;

class ProfitChart extends ChartWidget
{
    protected ?string $heading = 'Tren Laba Bersih (1 Tahun Terakhir)';
    protected static ?int $sort = 4;
    protected int | string | array $columnSpan = 'full';

    protected function getData(): array
    {
        if (! Schema::hasTable('transactions') || ! Schema::hasTable('categories')) {
            return [
                'datasets' => [[
                    'label' => 'Laba Bersih (Rp)',
                    'data' => array_fill(0, 12, 0),
                    'borderColor' => '#3b82f6',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.15)',
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

        $base = Transaction::query()
            ->join('categories', 'transactions.category_id', '=', 'categories.id')
            ->whereBetween('transactions.date', [$startDate, $endDate])
            ->selectRaw($selectRaw)
            ->groupByRaw($groupByRaw);

        $incomeResults = (clone $base)->where('categories.type', 'IN')->get()
            ->keyBy(fn($r) => $r->year . '-' . (int)$r->month);

        $expenseResults = (clone $base)->where('categories.type', 'OUT')->get()
            ->keyBy(fn($r) => $r->year . '-' . (int)$r->month);

        $data   = [];
        $labels = [];

        for ($i = 11; $i >= 0; $i--) {
            $month   = Carbon::now()->subMonths($i)->startOfMonth();
            $key     = $month->year . '-' . $month->month;
            $income  = $incomeResults->get($key)?->total ?? 0;
            $expense = $expenseResults->get($key)?->total ?? 0;
            $data[]  = $income - $expense;
            $labels[] = $month->translatedFormat('M Y');
        }

        return [
            'datasets' => [[
                'label'           => 'Laba Bersih (Rp)',
                'data'            => $data,
                'borderColor'     => '#3b82f6',
                'backgroundColor' => 'rgba(59, 130, 246, 0.15)',
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
