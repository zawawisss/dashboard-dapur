<?php

namespace App\Filament\Admin\Resources\Transactions\Pages;

use App\Filament\Admin\Resources\Transactions\TransactionResource;
use App\Filament\Admin\Widgets\BukuKasStats;
use Filament\Resources\Pages\ManageRecords;

class ManageTransactions extends ManageRecords
{
    protected static string $resource = TransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Tombol tambah transaksi dipindah ke FAB (Floating Action Button)
            // di pojok kanan bawah layar, lihat app/Livewire/FloatingTransactionButton.php
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            BukuKasStats::class,
        ];
    }
}
