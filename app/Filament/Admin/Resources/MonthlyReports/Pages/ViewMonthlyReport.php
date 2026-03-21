<?php

namespace App\Filament\Admin\Resources\MonthlyReports\Pages;

use App\Filament\Admin\Resources\MonthlyReports\MonthlyReportResource;
use App\Models\Transaction;
use Carbon\Carbon;
use Filament\Resources\Pages\Page;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;

class ViewMonthlyReport extends Page implements HasTable
{
    use InteractsWithRecord;
    use InteractsWithTable;

    protected static string $resource = MonthlyReportResource::class;

    protected string $view = 'filament.admin.resources.monthly-reports.pages.view-monthly-report';

    public function mount(int | string $record): void
    {
        $this->record = $this->resolveRecord($record);
    }

    public function table(Table $table): Table
    {
        $periodStart = Carbon::parse($this->record->period)->startOfMonth();
        $periodEnd = Carbon::parse($this->record->period)->endOfMonth();

        return $table
            ->query(
                Transaction::query()
                    ->where('user_id', $this->record->user_id)
                    ->whereBetween('date', [$periodStart, $periodEnd])
            )
            ->heading('Detail Transaksi - ' . $periodStart->translatedFormat('F Y'))
            ->columns([
                TextColumn::make('date')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Nama Transaksi')
                    ->searchable(),
                TextColumn::make('category.name')
                    ->label('Kategori')
                    ->badge()
                    ->color(fn (string $state, $record): string => $record->category->type === 'IN' ? 'success' : 'danger'),
                TextColumn::make('amount')
                    ->label('Nominal')
                    ->money('IDR', locale: 'id_ID')
                    ->color(fn ($record): string => $record->category->type === 'IN' ? 'success' : 'danger')
                    ->sortable(),
                TextColumn::make('note')
                    ->label('Catatan')
                    ->limit(30),
            ])
            ->defaultSort('date', 'asc');
    }

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Admin\Resources\MonthlyReports\Widgets\MonthlyReportStatsWidget::class,
        ];
    }

    public function getHeaderWidgetsData(): array
    {
        return [
            'record' => $this->record,
        ];
    }
}
