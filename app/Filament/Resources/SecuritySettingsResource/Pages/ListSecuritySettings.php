<?php

namespace App\Filament\Resources\SecuritySettingsResource\Pages;

use App\Filament\Resources\SecuritySettingsResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSecuritySettings extends ListRecords
{
    protected static string $resource = SecuritySettingsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
