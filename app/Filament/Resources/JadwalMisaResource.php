<?php

namespace App\Filament\Resources;

use App\Filament\Resources\JadwalMisaResource\Pages;
use App\Models\JadwalMisa;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class JadwalMisaResource extends Resource
{
    protected static ?string $model = JadwalMisa::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationGroup = 'Umat & Pelayanan';

    protected static ?string $navigationLabel = 'Jadwal Misa';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'Jadwal Misa';

    protected static ?string $pluralModelLabel = 'Jadwal Misa';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Waktu & Perayaan')
                    ->schema([
                        Forms\Components\DatePicker::make('tanggal')
                            ->label('Tanggal')
                            ->required(),
                        Forms\Components\TimePicker::make('waktu')
                            ->label('Waktu'),
                        Forms\Components\TextInput::make('hari')
                            ->label('Hari (Contoh: Minggu, Jumat Pertama)')
                            ->maxLength(50),
                        Forms\Components\TextInput::make('jenis_perayaan')
                            ->label('Nama / Jenis Perayaan')
                            ->placeholder('Contoh: Misa Hari Minggu Biasa IV')
                            ->maxLength(150),
                        Forms\Components\TextInput::make('jam_perayaan')
                            ->label('Jam Perayaan')
                            ->placeholder('06:00 WITA / 17:00 WITA')
                            ->maxLength(100),
                        Forms\Components\TextInput::make('jenis_misa')
                            ->label('Jenis Misa')
                            ->placeholder('Misa Bahasa Indonesia / Dawan / Latin')
                            ->maxLength(100),
                        Forms\Components\TextInput::make('tempat')
                            ->label('Tempat / Lokasi Misa')
                            ->placeholder('Gereja Pusat Paroki / Kapela St. Antonius')
                            ->maxLength(255),
                    ])->columns(2),

                Forms\Components\Section::make('Pelayan & Keterangan')
                    ->schema([
                        Forms\Components\Textarea::make('pelayan')
                            ->label('Imam / Petugas Liturgi')
                            ->rows(3),
                        Forms\Components\Textarea::make('intensi')
                            ->label('Intensi Umum')
                            ->rows(3),
                        Forms\Components\Textarea::make('catatan')
                            ->label('Catatan Tambahan')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tanggal')
                    ->label('Tanggal')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('hari')
                    ->label('Hari')
                    ->sortable(),
                Tables\Columns\TextColumn::make('jam_perayaan')
                    ->label('Jam'),
                Tables\Columns\TextColumn::make('jenis_perayaan')
                    ->label('Perayaan')
                    ->searchable()
                    ->limit(40),
                Tables\Columns\TextColumn::make('tempat')
                    ->label('Tempat')
                    ->searchable()
                    ->limit(30),
                Tables\Columns\TextColumn::make('pelayan')
                    ->label('Pelayan / Imam')
                    ->limit(30),
            ])
            ->filters([
                //
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
            ->defaultSort('tanggal', 'desc')
            ->paginated([15, 25, 50]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListJadwalMisas::route('/'),
            'create' => Pages\CreateJadwalMisa::route('/create'),
            'edit' => Pages\EditJadwalMisa::route('/{record}/edit'),
        ];
    }
}
