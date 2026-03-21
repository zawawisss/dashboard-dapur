<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Transaction;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class IncomeChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected ?string $heading = 'Tren Pemasukan (1 Tahun Terakhir)';
    protected static ?int $sort = 2;
    // Menguasai 1 kolom dari default 2 kolom pada filament dashboard
    protected int | string | array $columnSpan = 1;

    protected function getData(): array
    {
        $baseQuery = Transaction::query();
        $user = auth()->user();

        if ($user->role === 'ADMIN') {
            $currentFilter = $this->filters['role_filter'] ?? 'all';
            if ($currentFilter === 'admin') {
                $baseQuery->where('user_id', $user->id);
            } elseif ($currentFilter === 'investors') {
                $baseQuery->whereHas('user', function ($q) {
                    $q->where('role', 'USER');
                });
            } elseif (str_starts_with($currentFilter, 'investor_')) {
                $baseQuery->where('user_id', str_replace('investor_', '', $currentFilter));
            }
        } else {
            $baseQuery->where('user_id', $user->id);
        }

        $data = [];
        $labels = [];

        for ($i = 11; $i >= 0; $i--) {
            $monthStart = Carbon::now()->subMonths($i)->startOfMonth();
            $monthEnd = Carbon::now()->subMonths($i)->endOfMonth();

            $sum = (clone $baseQuery)->whereHas('category', function ($q) {
                $q->where('type', 'IN');
            })->whereBetween('date', [$monthStart, $monthEnd])->sum('amount');

            $data[] = $sum;
            $labels[] = $monthStart->translatedFormat('M Y');
        }

        return [
            'datasets' => [
                [
                    'label' => 'Total Pemasukan (Rp)',
                    'data' => $data,
                    'borderColor' => '#10b981', // Emerald 500 (Success)
                    'backgroundColor' => 'rgba(16, 185, 129, 0.2)',
                    'fill' => true,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
