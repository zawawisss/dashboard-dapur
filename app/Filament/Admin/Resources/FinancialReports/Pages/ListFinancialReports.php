<?php

namespace App\Filament\Admin\Resources\FinancialReports\Pages;

use App\Filament\Admin\Resources\FinancialReports\FinancialReportResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListFinancialReports extends ListRecords
{
    protected static string $resource = FinancialReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
