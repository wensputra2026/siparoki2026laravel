<?php

namespace App\Filament\Resources\SakramenResource\Pages;

use App\Filament\Resources\SakramenResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSakramen extends EditRecord
{
    protected static string $resource = SakramenResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
