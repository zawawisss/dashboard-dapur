<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Transaction;
use App\Models\FinancialReport;
use Carbon\Carbon;

class SyncFinancialReports extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reports:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync financial reports from transactions';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $transactions = Transaction::with('category')->get();
        // Kelompokkan berdasarkan user_id dan Tahun-Bulan
        $grouped = $transactions->groupBy(function ($item) {
            return $item->user_id . '_' . Carbon::parse($item->date)->format('Y-m');
        });

        foreach ($grouped as $key => $group) {
            list($userId, $yearMonth) = explode('_', $key);
            $period = Carbon::createFromFormat('Y-m', $yearMonth)->startOfMonth()->toDateString();

            $income = $group->where('category.type', 'IN')->sum('amount');
            $expense = $group->where('category.type', 'OUT')->sum('amount');
            $netProfit = $income - $expense;

            FinancialReport::updateOrCreate(
                [
                    'user_id' => $userId,
                    'period' => $period,
                ],
                [
                    'income' => $income,
                    'expense' => $expense,
                    'net_profit' => $netProfit,
                ]
            );
        }

        $this->info('Data laporan keuangan berhasil disinkronisasi dari transaksi.');
    }
}
