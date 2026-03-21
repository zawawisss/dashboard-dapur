<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Transaction;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

use Filament\Widgets\Concerns\InteractsWithPageFilters;

class IncomeExpenseStats extends BaseWidget
{
    use InteractsWithPageFilters;

    // Supaya widgetnya tampil di urutan paling atas di Dashboard
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $now = Carbon::now();
        
        $startOfThisMonth = $now->copy()->startOfMonth();
        $startOfLastMonth = $now->copy()->subMonth()->startOfMonth();
        $endOfLastMonth = $now->copy()->subMonth()->endOfMonth();

        // Inisiasi Query Dasar untuk Transaksi
        $baseQuery = Transaction::query();
        $user = auth()->user();

        // LOGIKA FILTER DAN AKSES ROLE
        if ($user->role === 'ADMIN') {
            // Evaluasi pilihan toggle filter Admin dari Page Filters
            $currentFilter = $this->filters['role_filter'] ?? 'all';
            
            if ($currentFilter === 'admin') {
                // Hanya menampilkan data punya Admin itu sendiri
                $baseQuery->where('user_id', $user->id);
            } elseif ($currentFilter === 'investors') {
                // Menampilkan semua data dari barisan Investor
                $baseQuery->whereHas('user', function ($q) {
                    $q->where('role', 'USER');
                });
            } elseif (str_starts_with($currentFilter, 'investor_')) {
                // Menampilkan data spesifik per-investor
                $investorId = str_replace('investor_', '', $currentFilter);
                $baseQuery->where('user_id', $investorId);
            }
            // Jika 'all', maka query tidak dibatasi (menampilkan keseluruhan data log)
            
        } else {
            // LOGIKA UNTUK INVESTOR (Hanya melihat datanya sendiri, tidak berhak melihat data admin dll)
            $baseQuery->where('user_id', $user->id);
        }

        // --- PEMASUKAN ---
        $incomeThisMonth = (clone $baseQuery)->whereHas('category', function ($query) {
            $query->where('type', 'IN');
        })->whereBetween('date', [$startOfThisMonth, $now])->sum('amount');

        $incomeLastMonth = (clone $baseQuery)->whereHas('category', function ($query) {
            $query->where('type', 'IN');
        })->whereBetween('date', [$startOfLastMonth, $endOfLastMonth])->sum('amount');

        // --- PENGELUARAN ---
        $expenseThisMonth = (clone $baseQuery)->whereHas('category', function ($query) {
            $query->where('type', 'OUT');
        })->whereBetween('date', [$startOfThisMonth, $now])->sum('amount');

        $expenseLastMonth = (clone $baseQuery)->whereHas('category', function ($query) {
            $query->where('type', 'OUT');
        })->whereBetween('date', [$startOfLastMonth, $endOfLastMonth])->sum('amount');

        // --- LABA BERSIH ---
        $profitThisMonth = $incomeThisMonth - $expenseThisMonth;
        $profitLastMonth = $incomeLastMonth - $expenseLastMonth;

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
