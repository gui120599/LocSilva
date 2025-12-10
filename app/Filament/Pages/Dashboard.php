<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    //protected static ?int $navigationSort = 15;

    public function getColumns(): int | array
{
    return 3;
}
}
