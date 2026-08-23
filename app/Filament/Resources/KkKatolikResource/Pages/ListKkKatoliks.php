<?php

namespace App\Filament\Resources\KkKatolikResource\Pages;

use App\Filament\Resources\KkKatolikResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListKkKatoliks extends ListRecords
{
    protected static string $resource = KkKatolikResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
