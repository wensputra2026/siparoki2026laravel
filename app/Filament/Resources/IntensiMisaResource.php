<?php

namespace App\Filament\Resources;

use App\Filament\Resources\IntensiMisaResource\Pages;
use App\Models\IntensiMisa;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class IntensiMisaResource extends Resource
{
    protected static ?string $model = IntensiMisa::class;

    protected static ?string $navigationIcon = 'heroicon-o-heart';

    protected static ?string $navigationGroup = 'Umat & Pelayanan';

    protected static ?string $navigationLabel = 'Intensi Misa';

    protected static ?int $navigationSort = 6;

    protected static ?string $modelLabel = 'Intensi Misa';

    protected static ?string $pluralModelLabel = 'Intensi Misa';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Card::make()
                    ->schema([
                        Forms\Components\TextInput::make('nama_pemohon')
                            ->label('Nama Pemohon')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Select::make('kategori_intensi')
                            ->label('Kategori')
                            ->options([
                                'Arwah' => 'Arwah',
                                'Syukur' => 'Syukur',
                                'Ulang Tahun' => 'Ulang Tahun',
                                'Kesembuhan' => 'Kesembuhan',
                                'Khusus' => 'Khusus',
                            ])
                            ->required(),
                        Forms\Components\DatePicker::make('tanggal_misa')
                            ->label('Tanggal Misa')
                            ->required(),
                        Forms\Components\TextInput::make('nominal_stipendium')
                            ->label('Nominal Stipendium')
                            ->numeric()
                            ->prefix('Rp')
                            ->default(0),
                        Forms\Components\Select::make('status_pembayaran')
                            ->label('Status Pembayaran')
                            ->options([
                                'Belum Bayar' => 'Belum Bayar',
                                'Lunas' => 'Lunas',
                            ])
                            ->default('Belum Bayar'),
                        Forms\Components\Textarea::make('deskripsi')
                            ->label('Deskripsi / Ujud Intensi')
                            ->required()
                            ->rows(3)
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama_pemohon')
                    ->label('Pemohon')
                    ->searchable()
                    ->sortable()
                    ->limit(30),
                Tables\Columns\TextColumn::make('kategori_intensi')
                    ->label('Kategori')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Arwah' => 'info',
                        'Syukur' => 'success',
                        'Ulang Tahun' => 'warning',
                        'Kesembuhan' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('tanggal_misa')
                    ->label('Tgl Misa')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('nominal_stipendium')
                    ->label('Stipendium')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status_pembayaran')
                    ->label('Bayar')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Lunas' => 'success',
                        'Belum Bayar' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('kategori_intensi')
                    ->label('Kategori')
                    ->options([
                        'Arwah' => 'Arwah',
                        'Syukur' => 'Syukur',
                        'Ulang Tahun' => 'Ulang Tahun',
                        'Kesembuhan' => 'Kesembuhan',
                        'Khusus' => 'Khusus',
                    ]),
                Tables\Filters\SelectFilter::make('status_pembayaran')
                    ->label('Status Bayar')
                    ->options([
                        'Belum Bayar' => 'Belum Bayar',
                        'Lunas' => 'Lunas',
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
            ->defaultSort('tanggal_misa', 'desc')
            ->paginated([15, 25, 50]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListIntensiMisas::route('/'),
            'create' => Pages\CreateIntensiMisa::route('/create'),
            'edit' => Pages\EditIntensiMisa::route('/{record}/edit'),
        ];
    }
}
