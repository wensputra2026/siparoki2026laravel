<?php

namespace App\Filament\Resources\PengajuanSakramenResource\Pages;

use App\Filament\Resources\PengajuanSakramenResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPengajuanSakramen extends EditRecord
{
    protected static string $resource = PengajuanSakramenResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
