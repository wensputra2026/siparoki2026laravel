<?php

namespace App\Filament\Widgets;

use App\Models\Umat;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class UmatChart extends ChartWidget
{
    protected static ?string $heading = 'Statistik Golongan Darah Umat';
    protected static ?int $sort = 3;
    protected static ?string $maxHeight = '300px';

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $golonganDarah = Umat::select('golongan_darah', \DB::raw('count(*) as total'))
            ->whereNotNull('golongan_darah')
            ->groupBy('golongan_darah')
            ->pluck('total', 'golongan_darah')
            ->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Jumlah Umat',
                    'data' => array_values($golonganDarah),
                    'backgroundColor' => '#3b82f6',
                ],
            ],
            'labels' => array_keys($golonganDarah),
        ];
    }
}
