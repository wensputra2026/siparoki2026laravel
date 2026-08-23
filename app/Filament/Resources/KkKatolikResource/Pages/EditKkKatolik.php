<?php

namespace App\Filament\Resources\KkKatolikResource\Pages;

use App\Filament\Resources\KkKatolikResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditKkKatolik extends EditRecord
{
    protected static string $resource = KkKatolikResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
