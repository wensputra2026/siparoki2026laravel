<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ArsipDigitalResource\Pages;
use App\Models\ArsipDigital;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ArsipDigitalResource extends Resource
{
    protected static ?string $model = ArsipDigital::class;

    protected static ?string $navigationIcon = 'heroicon-o-folder-open';

    protected static ?string $navigationGroup = 'Sekretariat Paroki';

    protected static ?string $navigationLabel = 'Arsip Digital';

    protected static ?int $navigationSort = 3;

    protected static ?string $modelLabel = 'Arsip Digital';

    protected static ?string $pluralModelLabel = 'Arsip Digital';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('judul')
                ->label('Judul')
                ->required()
                ->maxLength(255),
            Forms\Components\Select::make('kategori_arsip')
                ->label('Kategori Arsip')
                ->options([
                    'Surat' => 'Surat',
                    'Keputusan' => 'Keputusan',
                    'Notulensi' => 'Notulensi',
                    'Laporan' => 'Laporan',
                    'Dokumen Lain' => 'Dokumen Lain',
                ])
                ->required(),
            Forms\Components\Textarea::make('deskripsi')
                ->label('Deskripsi')
                ->rows(3),
            Forms\Components\TextInput::make('file_path')
                ->label('File Path')
                ->maxLength(500),
            Forms\Components\TextInput::make('ukuran_file')
                ->label('Ukuran File (bytes)')
                ->numeric(),
            Forms\Components\Select::make('hak_akses')
                ->label('Hak Akses')
                ->options([
                    'Public' => 'Public',
                    'Admin' => 'Admin',
                    'Staff' => 'Staff',
                ])
                ->default('Public'),
            Forms\Components\Select::make('privacy_level')
                ->label('Privacy Level')
                ->options([
                    'Public' => 'Public',
                    'Internal' => 'Internal',
                    'Confidential' => 'Confidential',
                    'Secret' => 'Secret',
                ])
                ->default('Public'),
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
                Tables\Columns\TextColumn::make('kategori_arsip')
                    ->label('Kategori')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Surat' => 'info',
                        'Keputusan' => 'warning',
                        'Notulensi' => 'success',
                        'Laporan' => 'primary',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('hak_akses')
                    ->label('Hak Akses')
                    ->badge(),
                Tables\Columns\TextColumn::make('privacy_level')
                    ->label('Privacy')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Public' => 'success',
                        'Internal' => 'info',
                        'Confidential' => 'warning',
                        'Secret' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('ukuran_file')
                    ->label('Ukuran')
                    ->formatStateUsing(fn ($state): string => $state ? number_format($state / 1024, 1) . ' KB' : '-'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('kategori_arsip')
                    ->label('Kategori'),
                Tables\Filters\SelectFilter::make('privacy_level')
                    ->label('Privacy'),
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
            'index' => Pages\ListArsipDigitals::route('/'),
            'create' => Pages\CreateArsipDigital::route('/create'),
            'edit' => Pages\EditArsipDigital::route('/{record}/edit'),
        ];
    }
}
