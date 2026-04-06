<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Transaction;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Schema;

class IncomeExpenseStats extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $now = Carbon::now();
        $fmt = fn ($n) => 'Rp ' . number_format($n, 0, ',', '.');

        if (! Schema::hasTable('transactions') || ! Schema::hasTable('categories')) {
            return [
                Stat::make('Total Pemasukan Bulan Ini', $fmt(0))
                    ->description('Data transaksi belum tersedia')
                    ->descriptionIcon('heroicon-m-information-circle')
                    ->color('gray'),
                Stat::make('Pengeluaran Bulan Ini', $fmt(0))
                    ->description('Data transaksi belum tersedia')
                    ->descriptionIcon('heroicon-m-information-circle')
                    ->color('gray'),
                Stat::make('Laba Bersih Bulan Ini', $fmt(0))
                    ->description('Data transaksi belum tersedia')
                    ->descriptionIcon('heroicon-m-information-circle')
                    ->color('gray'),
            ];
        }

        $startOfThisMonth = $now->copy()->startOfMonth();
        $startOfLastMonth = $now->copy()->subMonth()->startOfMonth();

        $results = Transaction::query()
            ->join('categories', 'transactions.category_id', '=', 'categories.id')
            ->whereIn('categories.type', ['IN', 'OUT'])
            ->whereBetween('transactions.date', [$startOfLastMonth, $now])
            ->selectRaw("
                categories.type,
                SUM(CASE WHEN transactions.date >= ? THEN transactions.amount ELSE 0 END) as this_month,
                SUM(CASE WHEN transactions.date <  ? THEN transactions.amount ELSE 0 END) as last_month
            ", [$startOfThisMonth, $startOfThisMonth])
            ->groupBy('categories.type')
            ->get()
            ->keyBy('type');

        $incomeThisMonth = $results->get('IN')?->this_month ?? 0;
        $incomeLastMonth = $results->get('IN')?->last_month ?? 0;
        $expenseThisMonth = $results->get('OUT')?->this_month ?? 0;
        $expenseLastMonth = $results->get('OUT')?->last_month ?? 0;
        $profitThisMonth = $incomeThisMonth - $expenseThisMonth;
        $profitLastMonth = $incomeLastMonth - $expenseLastMonth;

        $pctChange = function ($current, $previous) {
            if ($previous == 0) {
                return $current > 0 ? 100 : 0;
            }

            return (($current - $previous) / abs($previous)) * 100;
        };

        $incomePct = $pctChange($incomeThisMonth, $incomeLastMonth);
        $expensePct = $pctChange($expenseThisMonth, $expenseLastMonth);
        $profitPct = $pctChange($profitThisMonth, $profitLastMonth);

        $desc = fn ($pct) => abs(round($pct, 1)) . '% ' . ($pct >= 0 ? 'Naik' : 'Turun') . ' dibanding bulan lalu';

        return [
            Stat::make('Total Pemasukan Bulan Ini', $fmt($incomeThisMonth))
                ->description($desc($incomePct))
                ->descriptionIcon($incomePct >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($incomePct >= 0 ? 'success' : 'danger')
                ->chart([7, 2, 10, 3, 15, 4, 17]),

            Stat::make('Pengeluaran Bulan Ini', $fmt($expenseThisMonth))
                ->description($desc($expensePct))
                ->descriptionIcon($expensePct >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($expensePct >= 0 ? 'danger' : 'success')
                ->chart([15, 4, 17, 7, 2, 10, 3]),

            Stat::make('Laba Bersih Bulan Ini', $fmt($profitThisMonth))
                ->description($desc($profitPct))
                ->descriptionIcon($profitPct >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($profitThisMonth >= 0 ? 'success' : 'danger')
                ->chart([3, 10, 2, 7, 17, 4, 15]),
        ];
    }
}
