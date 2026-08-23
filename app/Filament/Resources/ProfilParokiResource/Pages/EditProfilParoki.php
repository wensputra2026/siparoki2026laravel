<?php

namespace App\Filament\Resources\ProfilParokiResource\Pages;

use App\Filament\Resources\ProfilParokiResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProfilParoki extends EditRecord
{
    protected static string $resource = ProfilParokiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
