<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LapakProdukResource\Pages;
use App\Models\LapakProduk;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class LapakProdukResource extends Resource
{
    protected static ?string $model = LapakProduk::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?string $navigationGroup = 'Lapak & Toko';

    protected static ?string $navigationLabel = 'Produk Lapak';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'Produk';

    protected static ?string $pluralModelLabel = 'Produk Lapak';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('nama_produk')
                ->label('Nama Produk')
                ->required()
                ->maxLength(255),
            Forms\Components\Textarea::make('deskripsi')
                ->label('Deskripsi')
                ->rows(3),
            Forms\Components\TextInput::make('harga')
                ->label('Harga')
                ->required()
                ->numeric()
                ->prefix('Rp'),
            Forms\Components\TextInput::make('stok')
                ->label('Stok')
                ->required()
                ->numeric()
                ->default(0),
            Forms\Components\Select::make('status_approval')
                ->label('Status Approval')
                ->options([
                    'Menunggu' => 'Menunggu',
                    'Disetujui' => 'Disetujui',
                    'Ditolak' => 'Ditolak',
                ])
                ->default('Menunggu'),
            Forms\Components\TextInput::make('penjual')
                ->label('Penjual')
                ->maxLength(255),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama_produk')
                    ->label('Nama Produk')
                    ->searchable()
                    ->sortable()
                    ->limit(40),
                Tables\Columns\TextColumn::make('harga')
                    ->label('Harga')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('stok')
                    ->label('Stok')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status_approval')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Menunggu' => 'warning',
                        'Disetujui' => 'success',
                        'Ditolak' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('penjual')
                    ->label('Penjual')
                    ->searchable()
                    ->limit(30),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status_approval')
                    ->label('Status Approval'),
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
            'index' => Pages\ListLapakProduks::route('/'),
            'create' => Pages\CreateLapakProduk::route('/create'),
            'edit' => Pages\EditLapakProduk::route('/{record}/edit'),
        ];
    }
}
