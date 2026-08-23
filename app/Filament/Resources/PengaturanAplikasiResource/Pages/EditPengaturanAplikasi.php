<?php

namespace App\Filament\Resources\PengaturanAplikasiResource\Pages;

use App\Filament\Resources\PengaturanAplikasiResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPengaturanAplikasi extends EditRecord
{
    protected static string $resource = PengaturanAplikasiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
