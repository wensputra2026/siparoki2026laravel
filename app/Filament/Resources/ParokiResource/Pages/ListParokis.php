<?php

namespace App\Filament\Resources\ParokiResource\Pages;

use App\Filament\Resources\ParokiResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListParokis extends ListRecords
{
    protected static string $resource = ParokiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
