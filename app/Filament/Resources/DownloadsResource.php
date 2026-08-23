<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DownloadsResource\Pages;
use App\Models\Downloads;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class DownloadsResource extends Resource
{
    protected static ?string $model = Downloads::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-down-tray';

    protected static ?string $navigationGroup = 'Website Paroki';

    protected static ?string $navigationLabel = 'Downloads';

    protected static ?int $navigationSort = 4;

    protected static ?string $modelLabel = 'Download';

    protected static ?string $pluralModelLabel = 'Downloads';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('judul')
                ->label('Judul')
                ->required()
                ->maxLength(255),
            Forms\Components\Select::make('kategori')
                ->label('Kategori')
                ->options([
                    'Formulir' => 'Formulir',
                    'Panduan' => 'Panduan',
                    'Surat Keputusan' => 'Surat Keputusan',
                    'Template' => 'Template',
                    'Lainnya' => 'Lainnya',
                ])
                ->required(),
            Forms\Components\TextInput::make('file_path')
                ->label('File Path')
                ->required()
                ->maxLength(500),
            Forms\Components\TextInput::make('file_size')
                ->label('Ukuran File (bytes)')
                ->numeric(),
            Forms\Components\TextInput::make('download_count')
                ->label('Jumlah Download')
                ->numeric()
                ->default(0),
            Forms\Components\Select::make('status')
                ->label('Status')
                ->options([
                    'Aktif' => 'Aktif',
                    'Tidak Aktif' => 'Tidak Aktif',
                ])
                ->default('Aktif'),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('judul')
                    ->label('Judul')
                    ->searchable()
                    ->sortable()
                    ->limit(40),
                Tables\Columns\TextColumn::make('kategori')
                    ->label('Kategori')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Formulir' => 'info',
                        'Panduan' => 'success',
                        'Surat Keputusan' => 'warning',
                        'Template' => 'primary',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('file_size')
                    ->label('Ukuran')
                    ->formatStateUsing(fn ($state): string => $state ? number_format($state / 1024, 1) . ' KB' : '-'),
                Tables\Columns\TextColumn::make('download_count')
                    ->label('Download')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Aktif' => 'success',
                        'Tidak Aktif' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('kategori')
                    ->label('Kategori'),
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated([15, 25, 50]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDownloads::route('/'),
            'create' => Pages\CreateDownload::route('/create'),
            'edit' => Pages\EditDownload::route('/{record}/edit'),
        ];
    }
}
