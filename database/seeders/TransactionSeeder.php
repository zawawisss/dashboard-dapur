<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;
use Illuminate\Support\Str;

class TransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::where('username', 'admin')->first();
        $investor = User::where('username', 'shifyannn')->first();
        
        $categories = Category::all();
        $inCategories = $categories->where('type', 'IN');
        $outCategories = $categories->where('type', 'OUT');

        $users = array_filter([$admin, $investor]);

        $startDate = Carbon::now()->subMonths(2);
        $endDate = Carbon::now();

        foreach ($users as $user) {
            // Membuat 30 transaksi per user (kira-kira 1 tiap 2 hari dalam 2 bulan)
            for ($i = 0; $i < 30; $i++) {
                $isIncome = rand(0, 1) === 1;
                $category = $isIncome ? $inCategories->random() : $outCategories->random();
                
                // Jumlah acak (IN: 1jt - 15jt, OUT: 50rb - 3jt)
                $amount = $isIncome ? rand(1000000, 15000000) : rand(50000, 3000000);
                
                // Tanggal acak 2 bulan terakhir
                $date = Carbon::createFromTimestamp(rand($startDate->timestamp, $endDate->timestamp));

                // Nama transaksi acak
                $inNames = ['Suntikan Dana', 'Modal Tambahan', 'Pendapatan Harian', 'Pemasukan Pasif'];
                $outNames = ['Beli Piring', 'Beli Sapu', 'Bayar Listrik', 'Gaji Karyawan', 'Belanja Bahan Baku'];
                $name = $isIncome ? $inNames[array_rand($inNames)] : $outNames[array_rand($outNames)];

                Transaction::create([
                    'name' => $name,
                    'user_id' => $user->id,
                    'category_id' => $category->id,
                    'amount' => $amount,
                    'date' => $date->format('Y-m-d'),
                    'note' => 'Transaksi ' . strtolower($category->name) . ' (' . Str::random(5) . ')',
                ]);
            }
        }
    }
}
