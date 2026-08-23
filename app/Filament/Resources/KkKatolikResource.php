<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KkKatolikResource\Pages;
use App\Models\KkKatolik;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class KkKatolikResource extends Resource
{
    protected static ?string $model = KkKatolik::class;

    protected static ?string $navigationIcon = 'heroicon-o-identification';

    protected static ?string $navigationGroup = 'Umat & Pelayanan';

    protected static ?string $navigationLabel = 'Data KK';

    protected static ?int $navigationSort = 3;

    protected static ?string $modelLabel = 'Kartu Keluarga Katolik';

    protected static ?string $pluralModelLabel = 'Kartu Keluarga Katolik';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Nomor & Identitas KK')
                    ->schema([
                        Forms\Components\TextInput::make('no_kk_kw')
                            ->label('No KK Gereja (KW)')
                            ->required()
                            ->maxLength(100),
                        Forms\Components\TextInput::make('no_kk_dukcapil')
                            ->label('No KK Dukcapil')
                            ->maxLength(100),
                        Forms\Components\TextInput::make('nik_pemilik')
                            ->label('NIK Kepala Keluarga')
                            ->maxLength(50),
                        Forms\Components\TextInput::make('nama_lahir_pemilik')
                            ->label('Nama Kepala Keluarga')
                            ->required()
                            ->maxLength(200),
                        Forms\Components\TextInput::make('nama_baptis_pemilik')
                            ->label('Nama Baptis Kepala Keluarga')
                            ->maxLength(150),
                        Forms\Components\TextInput::make('nama_pasangan')
                            ->label('Nama Pasangan (Istri/Suami)')
                            ->maxLength(200),
                    ])->columns(2),

                Forms\Components\Section::make('Wilayah Gerejawi & Domisili')
                    ->schema([
                        Forms\Components\Select::make('wilayah_id')
                            ->label('Wilayah')
                            ->relationship('wilayah', 'nama_wilayah')
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('lingkungan_id')
                            ->label('Lingkungan / KUB')
                            ->relationship('lingkungan', 'nama_lingkungan')
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('kapela_id')
                            ->label('Kapela / Stasi')
                            ->relationship('kapela', 'nama_kapela')
                            ->searchable()
                            ->preload(),
                        Forms\Components\TextInput::make('gereja_paroki')
                            ->label('Gereja Paroki')
                            ->default('Paroki Benlutu')
                            ->maxLength(150),
                        Forms\Components\Textarea::make('alamat_sekarang')
                            ->label('Alamat Domisili')
                            ->rows(3)
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('rt')
                            ->label('RT')
                            ->maxLength(10),
                        Forms\Components\TextInput::make('rw')
                            ->label('RW')
                            ->maxLength(10),
                        Forms\Components\TextInput::make('desa_kelurahan')
                            ->label('Desa / Kelurahan')
                            ->maxLength(100),
                        Forms\Components\TextInput::make('kecamatan')
                            ->label('Kecamatan')
                            ->maxLength(100),
                        Forms\Components\TextInput::make('kota_kabupaten')
                            ->label('Kota / Kabupaten')
                            ->maxLength(100),
                    ])->columns(3),

                Forms\Components\Section::make('Kontak & Status')
                    ->schema([
                        Forms\Components\TextInput::make('handphone')
                            ->label('No WhatsApp / HP')
                            ->maxLength(30),
                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->maxLength(150),
                        Forms\Components\Select::make('status_verifikasi')
                            ->label('Status Verifikasi')
                            ->options([
                                'Terverifikasi' => 'Terverifikasi',
                                'Belum Terverifikasi' => 'Belum Terverifikasi',
                                'Perlu Perbaikan' => 'Perlu Perbaikan',
                            ])
                            ->default('Terverifikasi'),
                        Forms\Components\Select::make('status_kk')
                            ->label('Status KK')
                            ->options([
                                'Aktif' => 'Aktif',
                                'Pindah' => 'Pindah',
                                'Nonaktif' => 'Nonaktif',
                            ])
                            ->default('Aktif'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('no_kk_kw')
                    ->label('No KK Gereja')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('nama_lahir_pemilik')
                    ->label('Kepala Keluarga')
                    ->searchable()
                    ->sortable()
                    ->description(fn (KkKatolik $record): ?string => $record->nama_baptis_pemilik ? "Baptis: {$record->nama_baptis_pemilik}" : null),
                Tables\Columns\TextColumn::make('lingkungan.nama_lingkungan')
                    ->label('Lingkungan / KUB')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('wilayah.nama_wilayah')
                    ->label('Wilayah')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('handphone')
                    ->label('No HP / WA')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status_verifikasi')
                    ->label('Verifikasi')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Terverifikasi' => 'success',
                        'Belum Terverifikasi' => 'warning',
                        'Perlu Perbaikan' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('status_kk')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Aktif' => 'success',
                        'Pindah' => 'warning',
                        default => 'danger',
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('wilayah_id')
                    ->label('Wilayah')
                    ->relationship('wilayah', 'nama_wilayah'),
                Tables\Filters\SelectFilter::make('lingkungan_id')
                    ->label('Lingkungan')
                    ->relationship('lingkungan', 'nama_lingkungan'),
                Tables\Filters\SelectFilter::make('status_verifikasi')
                    ->label('Verifikasi')
                    ->options([
                        'Terverifikasi' => 'Terverifikasi',
                        'Belum Terverifikasi' => 'Belum Terverifikasi',
                    ]),
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
            'index' => Pages\ListKkKatoliks::route('/'),
            'create' => Pages\CreateKkKatolik::route('/create'),
            'edit' => Pages\EditKkKatolik::route('/{record}/edit'),
        ];
    }
}
