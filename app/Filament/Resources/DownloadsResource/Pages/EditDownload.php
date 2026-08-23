<?php

namespace App\Filament\Resources\DownloadsResource\Pages;

use App\Filament\Resources\DownloadsResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDownload extends EditRecord
{
    protected static string $resource = DownloadsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
