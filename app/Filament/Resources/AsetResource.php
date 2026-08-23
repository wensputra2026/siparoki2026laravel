<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AsetResource\Pages;
use App\Models\Aset;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AsetResource extends Resource
{
    protected static ?string $model = Aset::class;

    protected static ?string $navigationIcon = 'heroicon-o-archive-box';

    protected static ?string $navigationGroup = 'Keuangan & Aset';

    protected static ?string $navigationLabel = 'Aset & Inventaris';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'Aset';

    protected static ?string $pluralModelLabel = 'Aset & Inventaris';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('kode_aset')
                ->label('Kode Aset')
                ->required()
                ->maxLength(50)
                ->unique(ignoreRecord: true),
            Forms\Components\TextInput::make('nama_aset')
                ->label('Nama Aset')
                ->required()
                ->maxLength(255)
                ->columnSpanFull(),
            Forms\Components\Textarea::make('deskripsi')
                ->label('Deskripsi')
                ->rows(3),
            Forms\Components\Select::make('kondisi')
                ->label('Kondisi')
                ->options([
                    'Baik' => 'Baik',
                    'Rusak Ringan' => 'Rusak Ringan',
                    'Rusak Berat' => 'Rusak Berat',
                    'Perlu Perbaikan' => 'Perlu Perbaikan',
                ])
                ->default('Baik'),
            Forms\Components\Select::make('status_aset')
                ->label('Status Aset')
                ->options([
                    'Aktif' => 'Aktif',
                    'Tidak Aktif' => 'Tidak Aktif',
                    'Dalam Perawatan' => 'Dalam Perawatan',
                    'Disewakan' => 'Disewakan',
                    'Dihapus' => 'Dihapus',
                ])
                ->default('Aktif'),
            Forms\Components\Select::make('level_pemilik')
                ->label('Level Pemilik')
                ->options([
                    'Paroki' => 'Paroki',
                    'Kapela' => 'Kapela',
                    'Lingkungan' => 'Lingkungan',
                ]),
            Forms\Components\TextInput::make('lokasi')
                ->label('Lokasi')
                ->maxLength(255),
            Forms\Components\TextInput::make('nilai_perolehan')
                ->label('Nilai Perolehan')
                ->numeric()
                ->prefix('Rp'),
            Forms\Components\DatePicker::make('tanggal_perolehan')
                ->label('Tanggal Perolehan'),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('kode_aset')
                    ->label('Kode')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('nama_aset')
                    ->label('Nama Aset')
                    ->searchable()
                    ->sortable()
                    ->limit(40),
                Tables\Columns\TextColumn::make('kondisi')
                    ->label('Kondisi')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Baik' => 'success',
                        'Rusak Ringan' => 'warning',
                        'Rusak Berat' => 'danger',
                        'Perlu Perbaikan' => 'info',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('status_aset')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Aktif' => 'success',
                        'Tidak Aktif' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('lokasi')
                    ->label('Lokasi')
                    ->searchable()
                    ->limit(30),
                Tables\Columns\TextColumn::make('nilai_perolehan')
                    ->label('Nilai')
                    ->money('IDR'),
                Tables\Columns\TextColumn::make('tanggal_perolehan')
                    ->label('Tgl Perolehan')
                    ->date('d/m/Y')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status_aset')
                    ->label('Status'),
                Tables\Filters\SelectFilter::make('kondisi')
                    ->label('Kondisi'),
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
            'index' => Pages\ListAsets::route('/'),
            'create' => Pages\CreateAset::route('/create'),
            'edit' => Pages\EditAset::route('/{record}/edit'),
        ];
    }
}
