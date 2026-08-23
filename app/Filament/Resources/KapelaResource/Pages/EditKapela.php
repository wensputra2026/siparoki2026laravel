<?php

namespace App\Filament\Resources\KapelaResource\Pages;

use App\Filament\Resources\KapelaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditKapela extends EditRecord
{
    protected static string $resource = KapelaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
