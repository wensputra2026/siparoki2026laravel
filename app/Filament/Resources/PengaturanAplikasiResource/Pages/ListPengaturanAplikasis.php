<?php

namespace App\Filament\Resources\PengaturanAplikasiResource\Pages;

use App\Filament\Resources\PengaturanAplikasiResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPengaturanAplikasis extends ListRecords
{
    protected static string $resource = PengaturanAplikasiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
