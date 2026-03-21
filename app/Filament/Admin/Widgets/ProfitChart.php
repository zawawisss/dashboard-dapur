<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Transaction;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class ProfitChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected ?string $heading = 'Tren Laba Bersih (1 Tahun Terakhir)';
    protected static ?int $sort = 4; // Berada di row paling bawah
    protected int | string | array $columnSpan = 'full'; // Lebar full width

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

            $income = (clone $baseQuery)->whereHas('category', function ($q) {
                $q->where('type', 'IN');
            })->whereBetween('date', [$monthStart, $monthEnd])->sum('amount');

            $expense = (clone $baseQuery)->whereHas('category', function ($q) {
                $q->where('type', 'OUT');
            })->whereBetween('date', [$monthStart, $monthEnd])->sum('amount');

            $data[] = $income - $expense;
            $labels[] = $monthStart->translatedFormat('M Y');
        }

        return [
            'datasets' => [
                [
                    'label' => 'Total Laba Bersih (Rp)',
                    'data' => $data,
                    'borderColor' => '#3b82f6', // Blue 500 (Primary/Info)
                    'backgroundColor' => 'rgba(59, 130, 246, 0.2)',
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
