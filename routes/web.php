<?php

use App\Filament\Admin\Pages\FinancialReport;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/admin');
});

Route::get('/admin/buku-kas/print', [FinancialReport::class, 'printView'])
    ->name('buku-kas.print');
