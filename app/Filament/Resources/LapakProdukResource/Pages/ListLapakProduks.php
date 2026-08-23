<?php

namespace App\Filament\Resources\LapakProdukResource\Pages;

use App\Filament\Resources\LapakProdukResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLapakProduks extends ListRecords
{
    protected static string $resource = LapakProdukResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
