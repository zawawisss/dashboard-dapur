<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $now = now();

        DB::table('users')
            ->whereNotIn('username', ['admin', 'investor'])
            ->delete();

        DB::table('users')->upsert([
            [
                'email' => 'admin@admin.com',
                'username' => 'admin',
                'password' => Hash::make('password'),
                'role' => 'ADMIN',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'email' => 'investor@investor.com',
                'username' => 'investor',
                'password' => Hash::make('password'),
                'role' => 'USER',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ], ['email'], ['username', 'password', 'role', 'updated_at']);

        $this->call([
            CategorySeeder::class,
            TransactionSeeder::class,
        ]);
    }
}
