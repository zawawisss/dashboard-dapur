<?php

namespace App\Filament\Admin\Resources\MonthlyReports\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Model;

class MonthlyReportStatsWidget extends BaseWidget
{
    public ?Model $record = null;

    protected function getStats(): array
    {
        if (! $this->record) {
            return [];
        }

        $now = \Carbon\Carbon::parse($this->record->period);
        $lastMonthPeriod = $now->copy()->subMonth()->format('Y-m-01');

        $lastMonthReport = \App\Models\MonthlyReport::where('user_id', $this->record->user_id)
            ->where('period', $lastMonthPeriod)
            ->first();

        $incomeThisMonth = $this->record->income ?? 0;
        $expenseThisMonth = $this->record->expense ?? 0;
        $profitThisMonth = $this->record->net_profit ?? 0;

        $incomeLastMonth = $lastMonthReport->income ?? 0;
        $expenseLastMonth = $lastMonthReport->expense ?? 0;
        $profitLastMonth = $lastMonthReport->net_profit ?? 0;

        // --- KALKULASI PERSENTASE ---
        $incomeDiff = $incomeThisMonth - $incomeLastMonth;
        $incomePercentage = $incomeLastMonth > 0 ? ($incomeDiff / $incomeLastMonth) * 100 : ($incomeThisMonth > 0 ? 100 : 0);
        
        $expenseDiff = $expenseThisMonth - $expenseLastMonth;
        $expensePercentage = $expenseLastMonth > 0 ? ($expenseDiff / $expenseLastMonth) * 100 : ($expenseThisMonth > 0 ? 100 : 0);

        $profitDiff = $profitThisMonth - $profitLastMonth;
        if ($profitLastMonth != 0) {
            $profitPercentage = ($profitDiff / abs($profitLastMonth)) * 100;
        } else {
            $profitPercentage = $profitThisMonth > 0 ? 100 : ($profitThisMonth < 0 ? -100 : 0);
        }

        // --- STYLING ---
        $incomeIcon = $incomeDiff >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down';
        $incomeColor = $incomeDiff >= 0 ? 'success' : 'danger';
        $incomeTrend = $incomeDiff >= 0 ? 'Naik' : 'Turun';

        $expenseIcon = $expenseDiff >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down';
        $expenseColor = $expenseDiff >= 0 ? 'danger' : 'success';
        $expenseTrend = $expenseDiff >= 0 ? 'Naik' : 'Turun';

        $profitIcon = $profitDiff >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down';
        $profitColor = $profitDiff >= 0 ? 'success' : 'danger';
        $profitTrend = $profitDiff >= 0 ? 'Naik' : 'Turun';

        return [
            Stat::make('Pemasukan Bulan Ini', 'Rp ' . number_format($incomeThisMonth, 0, ',', '.'))
                ->description(abs(round($incomePercentage, 1)) . '% ' . $incomeTrend . ' dibanding bulan lalu')
                ->descriptionIcon($incomeIcon)
                ->color($incomeColor)
                ->chart([7, 2, 10, 3, 15, 4, 17]), 

            Stat::make('Pengeluaran Bulan Ini', 'Rp ' . number_format($expenseThisMonth, 0, ',', '.'))
                ->description(abs(round($expensePercentage, 1)) . '% ' . $expenseTrend . ' dibanding bulan lalu')
                ->descriptionIcon($expenseIcon)
                ->color($expenseColor)
                ->chart([15, 4, 17, 7, 2, 10, 3]),

            Stat::make('Laba Bersih Bulan Ini', 'Rp ' . number_format($profitThisMonth, 0, ',', '.'))
                ->description(abs(round($profitPercentage, 1)) . '% ' . $profitTrend . ' dibanding bulan lalu')
                ->descriptionIcon($profitIcon)
                ->color($profitColor)
                ->chart([3, 10, 2, 7, 17, 4, 15]),
        ];
    }
}
