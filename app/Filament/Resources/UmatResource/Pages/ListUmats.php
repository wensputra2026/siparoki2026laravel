<?php

namespace App\Filament\Resources\UmatResource\Pages;

use App\Filament\Resources\UmatResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListUmats extends ListRecords
{
    protected static string $resource = UmatResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
