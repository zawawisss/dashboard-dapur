<?php

namespace App\Filament\Admin\Resources\FinancialReports\Schemas;

use Filament\Schemas\Schema;

class FinancialReportForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Forms\Components\Select::make('user_id')
                    ->relationship('user', 'username')
                    ->label('Nama Pengguna (Admin/Investor)')
                    ->required(),
                \Filament\Forms\Components\DatePicker::make('period')
                    ->label('Periode (Bulan/Tahun)')
                    ->displayFormat('F Y')
                    ->required(),
                \Filament\Forms\Components\TextInput::make('income')
                    ->label('Pemasukan')
                    ->numeric()
                    ->prefix('Rp')
                    ->default(0)
                    ->required(),
                \Filament\Forms\Components\TextInput::make('expense')
                    ->label('Pengeluaran')
                    ->numeric()
                    ->prefix('Rp')
                    ->default(0)
                    ->required(),
                \Filament\Forms\Components\TextInput::make('net_profit')
                    ->label('Laba Bersih')
                    ->numeric()
                    ->prefix('Rp')
                    ->default(0)
                    ->required(),
            ]);
    }
}
