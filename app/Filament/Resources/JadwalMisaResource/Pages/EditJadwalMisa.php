<?php

namespace App\Filament\Resources\JadwalMisaResource\Pages;

use App\Filament\Resources\JadwalMisaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditJadwalMisa extends EditRecord
{
    protected static string $resource = JadwalMisaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
