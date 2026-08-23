<?php

namespace App\Filament\Resources\BackupDatabaseResource\Pages;

use App\Filament\Resources\BackupDatabaseResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBackupDatabases extends ListRecords
{
    protected static string $resource = BackupDatabaseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
