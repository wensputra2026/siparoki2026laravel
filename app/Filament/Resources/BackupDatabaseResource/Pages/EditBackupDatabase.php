<?php

namespace App\Filament\Resources\BackupDatabaseResource\Pages;

use App\Filament\Resources\BackupDatabaseResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBackupDatabase extends EditRecord
{
    protected static string $resource = BackupDatabaseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
