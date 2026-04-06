<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            // 1. Drop view dulu (karena SQLite tidak bisa rename tabel yang ada dependensi view)
            DB::statement('DROP VIEW IF EXISTS monthly_reports_view');
            DB::statement('DROP TABLE IF EXISTS transactions_new');

            if (! Schema::hasTable('transactions')) {
                DB::statement('
                    CREATE TABLE transactions (
                        id          INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
                        user_id     INTEGER,
                        category_id INTEGER,
                        name        VARCHAR,
                        amount      INTEGER NOT NULL,
                        date        DATE NOT NULL,
                        note        VARCHAR,
                        created_at  DATETIME,
                        updated_at  DATETIME
                    )
                ');

                DB::statement('
                    CREATE VIEW monthly_reports_view AS
                    SELECT
                        strftime(\'%Y\', t.date) AS year,
                        strftime(\'%m\', t.date) AS month,
                        c.type,
                        SUM(t.amount)           AS total
                    FROM transactions t
                    JOIN categories c ON c.id = t.category_id
                    GROUP BY year, month, c.type
                ');

                return;
            }

            // 2. Buat tabel baru dengan kolom nullable
            DB::statement('
                CREATE TABLE transactions_new (
                    id          INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
                    user_id     INTEGER,
                    category_id INTEGER,
                    name        VARCHAR,
                    amount      INTEGER NOT NULL,
                    date        DATE NOT NULL,
                    note        VARCHAR,
                    created_at  DATETIME,
                    updated_at  DATETIME
                )
            ');

            // 3. Copy data
            DB::statement('
                INSERT INTO transactions_new
                    (id, user_id, category_id, name, amount, date, note, created_at, updated_at)
                SELECT id, user_id, category_id, name, amount, date, note, created_at, updated_at
                FROM transactions
            ');

            // 4. Drop tabel lama
            DB::statement('DROP TABLE transactions');

            // 5. Rename
            DB::statement('ALTER TABLE transactions_new RENAME TO transactions');

            // 6. Recreate view
            DB::statement('
                CREATE VIEW monthly_reports_view AS
                SELECT
                    strftime(\'%Y\', t.date) AS year,
                    strftime(\'%m\', t.date) AS month,
                    c.type,
                    SUM(t.amount)           AS total
                FROM transactions t
                JOIN categories c ON c.id = t.category_id
                GROUP BY year, month, c.type
            ');

        } else {
            // MySQL / PostgreSQL
            Schema::table('transactions', function (Blueprint $table) {
                $table->string('name')->nullable()->change();
                $table->string('note')->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            DB::statement('DROP VIEW IF EXISTS monthly_reports_view');
            DB::statement('DROP TABLE IF EXISTS transactions_new');

            DB::statement("UPDATE transactions SET name = '' WHERE name IS NULL");
            DB::statement("UPDATE transactions SET note = '' WHERE note IS NULL");

            DB::statement('
                CREATE TABLE transactions_new (
                    id          INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
                    user_id     INTEGER,
                    category_id INTEGER,
                    name        VARCHAR NOT NULL,
                    amount      INTEGER NOT NULL,
                    date        DATE NOT NULL,
                    note        VARCHAR NOT NULL,
                    created_at  DATETIME,
                    updated_at  DATETIME
                )
            ');
            DB::statement('
                INSERT INTO transactions_new SELECT * FROM transactions
            ');
            DB::statement('DROP TABLE transactions');
            DB::statement('ALTER TABLE transactions_new RENAME TO transactions');

            DB::statement('
                CREATE VIEW monthly_reports_view AS
                SELECT
                    strftime(\'%Y\', t.date) AS year,
                    strftime(\'%m\', t.date) AS month,
                    c.type,
                    SUM(t.amount)           AS total
                FROM transactions t
                JOIN categories c ON c.id = t.category_id
                GROUP BY year, month, c.type
            ');
        } else {
            Schema::table('transactions', function (Blueprint $table) {
                $table->string('name')->nullable(false)->change();
                $table->string('note')->nullable(false)->change();
            });
        }
    }
};
