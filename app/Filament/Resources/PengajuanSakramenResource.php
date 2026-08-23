<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PengajuanSakramenResource\Pages;
use App\Models\PengajuanSakramen;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PengajuanSakramenResource extends Resource
{
    protected static ?string $model = PengajuanSakramen::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-check';

    protected static ?string $navigationGroup = 'Umat & Pelayanan';

    protected static ?string $navigationLabel = 'Data Sakramen';

    protected static ?int $navigationSort = 4;

    protected static ?string $modelLabel = 'Pengajuan Sakramen';

    protected static ?string $pluralModelLabel = 'Pengajuan Sakramen';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Data Pemohon Sakramen')
                    ->schema([
                        Forms\Components\TextInput::make('nama_lengkap')
                            ->label('Nama Lengkap')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('whatsapp')
                            ->label('Nomor WhatsApp')
                            ->required()
                            ->maxLength(20),
                        Forms\Components\Select::make('tipe_sakramen')
                            ->label('Tipe Sakramen')
                            ->options([
                                'Baptis' => 'Baptis',
                                'Komuni Pertama' => 'Komuni Pertama',
                                'Krisma' => 'Krisma',
                                'Pernikahan' => 'Pernikahan',
                                'Baptis Dewasa' => 'Baptis Dewasa',
                            ])
                            ->required(),
                        Forms\Components\DatePicker::make('tanggal_pelaksanaan')
                            ->label('Tanggal Pelaksanaan'),
                        Forms\Components\Textarea::make('keterangan')
                            ->label('Catatan / Keterangan')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make('Status & Biaya Administrasi')
                    ->schema([
                        Forms\Components\TextInput::make('biaya_administrasi')
                            ->label('Biaya Admin')
                            ->numeric()
                            ->prefix('Rp')
                            ->default(25000),
                        Forms\Components\Select::make('status_pembayaran')
                            ->label('Status Pembayaran')
                            ->options([
                                'Menunggu Verifikasi' => 'Menunggu Verifikasi',
                                'Lunas' => 'Lunas',
                                'Ditolak' => 'Ditolak',
                            ])
                            ->default('Menunggu Verifikasi'),
                        Forms\Components\Select::make('status_pengajuan')
                            ->label('Status Pengajuan')
                            ->options([
                                'Pending' => 'Pending',
                                'Diproses' => 'Diproses',
                                'Selesai' => 'Selesai',
                                'Ditolak' => 'Ditolak',
                            ])
                            ->default('Pending'),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama_lengkap')
                    ->label('Nama Pemohon')
                    ->searchable()
                    ->sortable()
                    ->limit(30),
                Tables\Columns\TextColumn::make('whatsapp')
                    ->label('WhatsApp')
                    ->searchable(),
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
                Tables\Columns\TextColumn::make('tanggal_pelaksanaan')
                    ->label('Tgl Pelaksanaan')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('biaya_administrasi')
                    ->label('Biaya')
                    ->money('IDR'),
                Tables\Columns\TextColumn::make('status_pembayaran')
                    ->label('Bayar')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Lunas' => 'success',
                        'Ditolak' => 'danger',
                        default => 'warning',
                    }),
                Tables\Columns\TextColumn::make('status_pengajuan')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Selesai' => 'success',
                        'Diproses' => 'info',
                        'Ditolak' => 'danger',
                        default => 'warning',
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('tipe_sakramen')
                    ->label('Tipe Sakramen')
                    ->options([
                        'Baptis' => 'Baptis',
                        'Komuni Pertama' => 'Komuni Pertama',
                        'Krisma' => 'Krisma',
                        'Pernikahan' => 'Pernikahan',
                        'Baptis Dewasa' => 'Baptis Dewasa',
                    ]),
                Tables\Filters\SelectFilter::make('status_pengajuan')
                    ->label('Status Pengajuan')
                    ->options([
                        'Pending' => 'Pending',
                        'Diproses' => 'Diproses',
                        'Selesai' => 'Selesai',
                        'Ditolak' => 'Ditolak',
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
            'index' => Pages\ListPengajuanSakramens::route('/'),
            'create' => Pages\CreatePengajuanSakramen::route('/create'),
            'edit' => Pages\EditPengajuanSakramen::route('/{record}/edit'),
        ];
    }
}
