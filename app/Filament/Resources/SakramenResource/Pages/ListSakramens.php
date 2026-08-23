<?php

namespace App\Filament\Resources\SakramenResource\Pages;

use App\Filament\Resources\SakramenResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSakramens extends ListRecords
{
    protected static string $resource = SakramenResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
