<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = \Illuminate\Support\Facades\DB::getDriverName();
        
        if ($driver === 'sqlite') {
            \Illuminate\Support\Facades\DB::statement("DROP VIEW IF EXISTS monthly_reports_view;");
            \Illuminate\Support\Facades\DB::statement("
                CREATE VIEW monthly_reports_view AS
                SELECT 
                    transactions.user_id || '-' || MAX(strftime('%Y-%m', transactions.date)) as id,
                    transactions.user_id,
                    MAX(strftime('%Y-%m-01', transactions.date)) as period,
                    SUM(CASE WHEN categories.type = 'IN' THEN transactions.amount ELSE 0 END) as income,
                    SUM(CASE WHEN categories.type = 'OUT' THEN transactions.amount ELSE 0 END) as expense,
                    SUM(CASE WHEN categories.type = 'IN' THEN transactions.amount ELSE -transactions.amount END) as net_profit
                FROM transactions
                JOIN categories ON transactions.category_id = categories.id
                GROUP BY transactions.user_id, strftime('%Y-%m', transactions.date)
            ");
        } else {
            \Illuminate\Support\Facades\DB::statement("
                CREATE OR REPLACE VIEW monthly_reports_view AS
                SELECT 
                    CONCAT(transactions.user_id, '-', MAX(DATE_FORMAT(transactions.date, '%Y-%m'))) as id,
                    transactions.user_id,
                    MAX(DATE_FORMAT(transactions.date, '%Y-%m-01')) as period,
                    SUM(CASE WHEN categories.type = 'IN' THEN transactions.amount ELSE 0 END) as income,
                    SUM(CASE WHEN categories.type = 'OUT' THEN transactions.amount ELSE 0 END) as expense,
                    SUM(CASE WHEN categories.type = 'IN' THEN transactions.amount ELSE -transactions.amount END) as net_profit
                FROM transactions
                JOIN categories ON transactions.category_id = categories.id
                GROUP BY transactions.user_id, DATE_FORMAT(transactions.date, '%Y-%m')
            ");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        \Illuminate\Support\Facades\DB::statement("DROP VIEW IF EXISTS monthly_reports_view");
    }
};
