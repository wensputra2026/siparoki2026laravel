<?php

namespace App\Filament\Resources\JadwalMisaResource\Pages;

use App\Filament\Resources\JadwalMisaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListJadwalMisas extends ListRecords
{
    protected static string $resource = JadwalMisaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
