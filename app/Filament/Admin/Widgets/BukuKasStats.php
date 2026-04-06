<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Transaction;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Number;

class BukuKasStats extends BaseWidget
{
    protected static ?int $sort = 1;

    // Jangan tampil di dashboard global; hanya muncul di halaman Buku Kas.
    protected static bool $isDiscovered = false;

    protected function getStats(): array
    {
        $now = Carbon::now();

        if (! Schema::hasTable('transactions') || ! Schema::hasTable('categories')) {
            return [
                Stat::make('Pemasukan Bulan Ini', 'Rp 0')
                    ->description('Data transaksi belum tersedia')
                    ->descriptionIcon('heroicon-m-information-circle')
                    ->color('gray'),
                Stat::make('Pengeluaran Bulan Ini', 'Rp 0')
                    ->description('Data transaksi belum tersedia')
                    ->descriptionIcon('heroicon-m-information-circle')
                    ->color('gray'),
                Stat::make('Laba Bersih Bulan Ini', 'Rp 0')
                    ->description('Data transaksi belum tersedia')
                    ->descriptionIcon('heroicon-m-information-circle')
                    ->color('gray'),
                Stat::make('Saldo Kas Keseluruhan', 'Rp 0')
                    ->description('Data transaksi belum tersedia')
                    ->descriptionIcon('heroicon-m-information-circle')
                    ->color('gray'),
            ];
        }

        $totalPemasukan = Transaction::whereHas('category', fn ($q) => $q->where('type', 'IN'))
            ->whereMonth('date', $now->month)
            ->whereYear('date', $now->year)
            ->sum('amount');

        $totalPengeluaran = Transaction::whereHas('category', fn ($q) => $q->where('type', 'OUT'))
            ->whereMonth('date', $now->month)
            ->whereYear('date', $now->year)
            ->sum('amount');

        $saldo = $totalPemasukan - $totalPengeluaran;

        $totalPemasukanAll = Transaction::whereHas('category', fn ($q) => $q->where('type', 'IN'))->sum('amount');
        $totalPengeluaranAll = Transaction::whereHas('category', fn ($q) => $q->where('type', 'OUT'))->sum('amount');
        $saldoAll = $totalPemasukanAll - $totalPengeluaranAll;

        return [
            Stat::make('Pemasukan Bulan Ini', 'Rp ' . Number::format($totalPemasukan, 0, locale: 'id'))
                ->description('Total pemasukan ' . $now->translatedFormat('F Y'))
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),

            Stat::make('Pengeluaran Bulan Ini', 'Rp ' . Number::format($totalPengeluaran, 0, locale: 'id'))
                ->description('Total pengeluaran ' . $now->translatedFormat('F Y'))
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color('danger'),

            Stat::make('Laba Bersih Bulan Ini', ($saldo >= 0 ? '+' : '') . 'Rp ' . Number::format($saldo, 0, locale: 'id'))
                ->description('Pemasukan - Pengeluaran bulan ini')
                ->descriptionIcon($saldo >= 0 ? 'heroicon-m-check-circle' : 'heroicon-m-exclamation-circle')
                ->color($saldo >= 0 ? 'success' : 'danger'),

            Stat::make('Saldo Kas Keseluruhan', ($saldoAll >= 0 ? '+' : '') . 'Rp ' . Number::format($saldoAll, 0, locale: 'id'))
                ->description('Akumulasi seluruh pemasukan dan pengeluaran')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color($saldoAll >= 0 ? 'info' : 'warning'),
        ];
    }
}
