<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use Illuminate\Support\Facades\DB;

class PompaData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:pompa-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pindahkan (Pompa) data dari SQLite Lokal ke Database Cloud (MySQL/PostgreSQL)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tables = ['users', 'categories', 'transactions'];

        $this->info('Memulai pemindahan data ke Cloud (Mode Aman)...');

        // Matikan pengecekan Foreign Key agar tidak Error di MySQL
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        foreach ($tables as $table) {
            $this->info("Pompa data dari tabel [{$table}]...");

            // Ambil semua data dari koneksi SQLite Pumper
            $rows = DB::connection('sqlite_pumper')->table($table)->get();

            foreach ($rows as $row) {
                $data = (array) $row;
                
                // Gunakan updateOrInsert agar ID tetap sama (Penting untuk relasi)
                DB::table($table)->updateOrInsert(['id' => $data['id']], $data);
            }

            $count = count($rows);
            $this->info("Berhasil memindahkan {$count} baris dari tabel [{$table}].");
        }

        // Aktifkan kembali pengecekan Foreign Key
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $this->info('--- PEMINDAHAN SELESAI ---');
        $this->info('Catatan Kas Anda kini sudah aman di Cloud!');
    }
}
