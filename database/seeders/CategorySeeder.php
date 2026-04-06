<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            // Pemasukan (IN)
            ['name' => 'Pemasukan', 'type' => 'IN'],
            ['name' => 'Modal Awal', 'type' => 'IN'],
            ['name' => 'Lain-lain (IN)', 'type' => 'IN'],
            
            // Pengeluaran (OUT)
            ['name' => 'Bahan Baku', 'type' => 'OUT'],
            ['name' => 'Operasional', 'type' => 'OUT'],
            ['name' => 'Gaji Karyawan', 'type' => 'OUT'],
            ['name' => 'Listrik & Air', 'type' => 'OUT'],
            ['name' => 'Perawatan Alat', 'type' => 'OUT'],
            ['name' => 'Lain-lain (OUT)', 'type' => 'OUT'],
        ];

        foreach ($categories as $category) {
            Category::firstOrCreate(['name' => $category['name']], $category);
        }
    }
}
