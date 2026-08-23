<?php

namespace App\Filament\Resources\UmatResource\Pages;

use App\Filament\Resources\UmatResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUmat extends EditRecord
{
    protected static string $resource = UmatResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
