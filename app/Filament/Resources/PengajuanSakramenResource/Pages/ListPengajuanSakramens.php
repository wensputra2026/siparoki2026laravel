<?php

namespace App\Filament\Resources\PengajuanSakramenResource\Pages;

use App\Filament\Resources\PengajuanSakramenResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPengajuanSakramens extends ListRecords
{
    protected static string $resource = PengajuanSakramenResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
