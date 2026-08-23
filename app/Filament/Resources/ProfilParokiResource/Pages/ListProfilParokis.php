<?php

namespace App\Filament\Resources\ProfilParokiResource\Pages;

use App\Filament\Resources\ProfilParokiResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProfilParokis extends ListRecords
{
    protected static string $resource = ProfilParokiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
