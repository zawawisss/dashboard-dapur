<?php

namespace App\Filament\Admin\Resources\FinancialReports\Pages;

use App\Filament\Admin\Resources\FinancialReports\FinancialReportResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditFinancialReport extends EditRecord
{
    protected static string $resource = FinancialReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
