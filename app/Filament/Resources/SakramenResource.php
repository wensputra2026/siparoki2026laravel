<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SakramenResource\Pages;
use App\Models\Sakramen;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SakramenResource extends Resource
{
    protected static ?string $model = Sakramen::class;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    protected static ?string $navigationGroup = 'Umat & Pelayanan';

    protected static ?string $navigationLabel = 'Buku Sakramen';

    protected static ?int $navigationSort = 3;

    protected static ?string $modelLabel = 'Buku Sakramen';

    protected static ?string $pluralModelLabel = 'Buku Register Sakramen';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Sakramen & Penerima')
                    ->schema([
                        Forms\Components\Select::make('id_umat')
                            ->label('Umat Penerima Sakramen')
                            ->relationship('umat', 'nama_lengkap')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\Select::make('tipe_sakramen')
                            ->label('Tipe Sakramen')
                            ->options([
                                'Baptis' => 'Baptis',
                                'Komuni Pertama' => 'Komuni Pertama',
                                'Krisma' => 'Krisma',
                                'Pernikahan' => 'Pernikahan',
                                'Pengurapan Orang Sakit' => 'Pengurapan Orang Sakit',
                                'Tahbisan / Imamat' => 'Tahbisan / Imamat',
                            ])
                            ->required(),
                        Forms\Components\TextInput::make('no_surat')
                            ->label('Nomor Surat / Sertifikat')
                            ->maxLength(100),
                        Forms\Components\DatePicker::make('tanggal')
                            ->label('Tanggal Penerimaan')
                            ->required(),
                        Forms\Components\TextInput::make('tempat')
                            ->label('Tempat / Gereja')
                            ->maxLength(200),
                        Forms\Components\TextInput::make('pastor')
                            ->label('Pastor Pelayan Sakramen')
                            ->maxLength(200),
                    ])->columns(2),

                Forms\Components\Section::make('Register Buku (Liber Matrikul)')
                    ->schema([
                        Forms\Components\TextInput::make('liber_vol')
                            ->label('Liber (Volume / Buku)')
                            ->maxLength(50),
                        Forms\Components\TextInput::make('liber_hal')
                            ->label('Halaman')
                            ->maxLength(50),
                        Forms\Components\TextInput::make('liber_no')
                            ->label('Nomor Akta')
                            ->maxLength(50),
                        Forms\Components\Select::make('status_liber')
                            ->label('Status Liber')
                            ->options([
                                'Tercatat' => 'Tercatat',
                                'Belum Tercatat' => 'Belum Tercatat',
                            ])
                            ->default('Tercatat'),
                    ])->columns(4),

                Forms\Components\Section::make('Data Tambahan (Baptis / Pernikahan)')
                    ->schema([
                        Forms\Components\TextInput::make('wali_baptis')
                            ->label('Wali Baptis')
                            ->maxLength(200),
                        Forms\Components\TextInput::make('nama_pasangan')
                            ->label('Nama Pasangan (Pernikahan)')
                            ->maxLength(200),
                        Forms\Components\TextInput::make('saksi_1')
                            ->label('Saksi 1')
                            ->maxLength(150),
                        Forms\Components\TextInput::make('saksi_2')
                            ->label('Saksi 2')
                            ->maxLength(150),
                        Forms\Components\Textarea::make('catatan')
                            ->label('Catatan / Keterangan')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('umat.nama_lengkap')
                    ->label('Penerima Sakramen')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tipe_sakramen')
                    ->label('Sakramen')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Baptis' => 'info',
                        'Komuni Pertama' => 'success',
                        'Krisma' => 'warning',
                        'Pernikahan' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('no_surat')
                    ->label('No Surat / Akta')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tanggal')
                    ->label('Tanggal')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('pastor')
                    ->label('Pastor')
                    ->searchable(),
                Tables\Columns\TextColumn::make('liber_vol')
                    ->label('Buku/Hal/No')
                    ->formatStateUsing(fn ($state, Sakramen $record) => "Vol: {$record->liber_vol} | Hal: {$record->liber_hal} | No: {$record->liber_no}"),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('tipe_sakramen')
                    ->label('Tipe Sakramen')
                    ->options([
                        'Baptis' => 'Baptis',
                        'Komuni Pertama' => 'Komuni Pertama',
                        'Krisma' => 'Krisma',
                        'Pernikahan' => 'Pernikahan',
                        'Pengurapan Orang Sakit' => 'Pengurapan Orang Sakit',
                        'Tahbisan / Imamat' => 'Tahbisan / Imamat',
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
            'index' => Pages\ListSakramens::route('/'),
            'create' => Pages\CreateSakramen::route('/create'),
            'edit' => Pages\EditSakramen::route('/{record}/edit'),
        ];
    }
}
