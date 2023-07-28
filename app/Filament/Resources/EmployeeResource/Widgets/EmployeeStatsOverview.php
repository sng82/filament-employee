<?php

namespace App\Filament\Resources\EmployeeResource\Widgets;

use App\Models\Department;
use App\Models\Employee;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;

class EmployeeStatsOverview extends BaseWidget
{
    protected function getCards(): array
    {
        $itSupport = Department::where('name', 'IT Support')->withCount('employees')->first();
        $software = Department::where('name', 'software')->withCount('employees')->first();
        $workshop = Department::where('name', 'Workshop')->withCount('employees')->first();
        return [
            Card::make('Total Employees', Employee::all()->count()),
            Card::make('IT Support', $itSupport->employees_count),
            Card::make('Software', $software->employees_count),
            Card::make('Workshop', $workshop->employees_count),
        ];
    }
}
