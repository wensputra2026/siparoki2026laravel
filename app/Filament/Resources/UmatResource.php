<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UmatResource\Pages;
use App\Models\Umat;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class UmatResource extends Resource
{
    protected static ?string $model = Umat::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'Umat & Pelayanan';

    protected static ?string $navigationLabel = 'Pelayanan Paroki';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'Umat';

    protected static ?string $pluralModelLabel = 'Data Umat';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Data Pribadi')
                    ->schema([
                        Forms\Components\TextInput::make('niu')
                            ->label('NIU (Nomor Induk Umat)')
                            ->maxLength(30),
                        Forms\Components\TextInput::make('nik')
                            ->label('NIK (KTP)')
                            ->maxLength(20),
                        Forms\Components\TextInput::make('nama_lengkap')
                            ->label('Nama Lengkap')
                            ->required()
                            ->maxLength(200),
                        Forms\Components\TextInput::make('nama_baptis')
                            ->label('Nama Baptis')
                            ->maxLength(150),
                        Forms\Components\TextInput::make('nama_lahir')
                            ->label('Nama Lahir')
                            ->maxLength(200),
                        Forms\Components\TextInput::make('nama_marga')
                            ->label('Marga / Fam')
                            ->maxLength(100),
                        Forms\Components\Select::make('jenis_kelamin')
                            ->label('Jenis Kelamin')
                            ->options([
                                'Laki-Laki' => 'Laki-Laki',
                                'Perempuan' => 'Perempuan',
                            ])
                            ->required()
                            ->default('Laki-Laki'),
                        Forms\Components\DatePicker::make('tanggal_lahir')
                            ->label('Tanggal Lahir'),
                        Forms\Components\TextInput::make('tempat_lahir')
                            ->label('Tempat Lahir')
                            ->maxLength(100),
                        Forms\Components\Select::make('golongan_darah')
                            ->label('Golongan Darah')
                            ->options([
                                'A' => 'A',
                                'B' => 'B',
                                'AB' => 'AB',
                                'O' => 'O',
                                '-' => 'Tidak Tahu',
                            ]),
                    ])->columns(2),

                Forms\Components\Section::make('Keluarga & Gereja')
                    ->schema([
                        Forms\Components\Select::make('kk_id')
                            ->label('Kartu Keluarga (KK Katolik)')
                            ->relationship('kk', 'no_kk_kw')
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('hubungan_keluarga')
                            ->label('Hubungan Keluarga')
                            ->options([
                                'Kepala Keluarga' => 'Kepala Keluarga',
                                'Istri' => 'Istri',
                                'Anak' => 'Anak',
                                'Famili Lain' => 'Famili Lain',
                            ]),
                        Forms\Components\Select::make('status_menikah')
                            ->label('Status Pernikahan')
                            ->options([
                                'Belum Menikah' => 'Belum Menikah',
                                'Menikah Gereja' => 'Menikah Gereja',
                                'Menikah Sipil' => 'Menikah Sipil',
                                'Janda / Duda' => 'Janda / Duda',
                            ]),
                        Forms\Components\TextInput::make('pekerjaan')
                            ->label('Pekerjaan')
                            ->maxLength(100),
                        Forms\Components\TextInput::make('pendidikan_saat_ini')
                            ->label('Pendidikan Terakhir')
                            ->maxLength(100),
                    ])->columns(2),

                Forms\Components\Section::make('Data Kontak & Status')
                    ->schema([
                        Forms\Components\TextInput::make('handphone')
                            ->label('Nomor WhatsApp / HP')
                            ->maxLength(20),
                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->maxLength(150),
                        Forms\Components\Select::make('status_umat')
                            ->label('Status Keberadaan')
                            ->options([
                                'Aktif' => 'Aktif',
                                'Pindah KUB' => 'Pindah KUB',
                                'Pindah Wilayah' => 'Pindah Wilayah',
                                'Pindah Paroki' => 'Pindah Paroki',
                                'Meninggal' => 'Meninggal',
                                'Tidak Aktif' => 'Tidak Aktif',
                            ])
                            ->default('Aktif'),
                        Forms\Components\Toggle::make('status_aktif')
                            ->label('Status Aktif')
                            ->default(true),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('niu')
                    ->label('NIU')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('nama_lengkap')
                    ->label('Nama Umat')
                    ->searchable()
                    ->sortable()
                    ->description(fn (Umat $record): ?string => $record->nama_baptis ? "Baptis: {$record->nama_baptis}" : null),
                Tables\Columns\TextColumn::make('jenis_kelamin')
                    ->label('JK')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Laki-Laki' => 'info',
                        'Perempuan' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('tanggal_lahir')
                    ->label('Tgl Lahir')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('handphone')
                    ->label('HP / WA')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status_umat')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Aktif' => 'success',
                        'Meninggal' => 'danger',
                        default => 'warning',
                    }),
                Tables\Columns\IconColumn::make('status_aktif')
                    ->label('Aktif')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('jenis_kelamin')
                    ->label('Jenis Kelamin')
                    ->options([
                        'Laki-Laki' => 'Laki-Laki',
                        'Perempuan' => 'Perempuan',
                    ]),
                Tables\Filters\SelectFilter::make('status_umat')
                    ->label('Status Umat')
                    ->options([
                        'Aktif' => 'Aktif',
                        'Meninggal' => 'Meninggal',
                        'Pindah Paroki' => 'Pindah Paroki',
                        'Tidak Aktif' => 'Tidak Aktif',
                    ]),
                Tables\Filters\TernaryFilter::make('status_aktif')
                    ->label('Status Aktif'),
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
            ->defaultSort('nama_lengkap', 'asc')
            ->paginated([15, 25, 50, 100]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUmats::route('/'),
            'create' => Pages\CreateUmat::route('/create'),
            'edit' => Pages\EditUmat::route('/{record}/edit'),
        ];
    }
}
