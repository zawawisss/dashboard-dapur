<?php

namespace App\Filament\Admin\Pages;

use App\Filament\Admin\Resources\Categories\CategoryResource;
use App\Filament\Admin\Widgets\BukuKasStats;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;

class Dashboard extends \Filament\Pages\Dashboard
{
    protected static ?string $title = 'Beranda Operasional';
    protected static ?string $navigationLabel = 'Beranda';

    public function getColumns(): int | array
    {
        return [
            'default' => 1,
            'md' => 2,
        ];
    }

    public function getWidgets(): array
    {
        return [
            BukuKasStats::class,
        ];
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                View::make('filament.admin.pages.dashboard-overview')
                    ->viewData([
                        'cashBookUrl' => FinancialReport::getUrl(),
                        'categoryUrl' => CategoryResource::canAccess() ? CategoryResource::getUrl() : null,
                        'profileUrl' => Setting::getUrl(),
                    ]),
                $this->getWidgetsContentComponent(),
            ]);
    }
}
