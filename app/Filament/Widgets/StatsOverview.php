<?php

namespace App\Filament\Widgets;

use App\Models\KkKatolik;
use App\Models\Lingkungan;
use App\Models\Umat;
use App\Models\Wilayah;
use App\Models\Kapela;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 0;

    protected function getStats(): array
    {
        $totalUmat      = Umat::count();
        $totalKK        = KkKatolik::count();
        $totalLingkungan = Lingkungan::count();
        $totalWilayah   = Wilayah::count();
        $totalKapela    = Kapela::count();

        return [
            Stat::make('Total Umat Terdata', number_format($totalUmat))
                ->description('Data umat paroki')
                ->descriptionIcon('heroicon-m-users')
                ->color('success')
                ->chart([7, 3, 4, 5, 6, 3, 5, 8]),

            Stat::make('Kepala Keluarga (KK)', number_format($totalKK))
                ->description('Kartu keluarga terdaftar')
                ->descriptionIcon('heroicon-m-home-modern')
                ->color('info')
                ->chart([2, 4, 3, 5, 4, 6, 5, 7]),

            Stat::make('Lingkungan / KUB', number_format($totalLingkungan))
                ->description('Basis komunitas umat basis')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('warning')
                ->chart([1, 2, 3, 2, 4, 3, 5, 4]),

            Stat::make('Wilayah & Kapela', $totalWilayah . ' / ' . $totalKapela)
                ->description('Struktur wilayah gerejawi')
                ->descriptionIcon('heroicon-m-map-pin')
                ->color('primary')
                ->chart([3, 4, 5, 4, 6, 5, 7, 6]),
        ];
    }
}
