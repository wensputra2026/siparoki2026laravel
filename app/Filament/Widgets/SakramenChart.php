<?php

namespace App\Filament\Widgets;

use App\Models\SakramenUmat;
use Filament\Widgets\ChartWidget;

class SakramenChart extends ChartWidget
{
    protected static ?string $heading = 'Capaian Sakramen Paroki';
    protected static ?int $sort = 2;
    protected static ?string $maxHeight = '300px';

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getData(): array
    {
        $baptis     = SakramenUmat::where('is_baptis', true)->count();
        $komuni     = SakramenUmat::where('is_komuni', true)->count();
        $krisma     = SakramenUmat::where('is_krisma', true)->count();
        $perkawinan = SakramenUmat::where('is_nikah', true)->count();

        return [
            'datasets' => [
                [
                    'data'            => [$baptis, $komuni, $krisma, $perkawinan],
                    'backgroundColor' => ['#3b82f6', '#10b981', '#f59e0b', '#ef4444'],
                ],
            ],
            'labels' => ['Baptis', 'Komuni Pertama', 'Krisma', 'Pernikahan'],
        ];
    }
}
