<?php

namespace App\Filament\Resources\DownloadsResource\Pages;

use App\Filament\Resources\DownloadsResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDownloads extends ListRecords
{
    protected static string $resource = DownloadsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
