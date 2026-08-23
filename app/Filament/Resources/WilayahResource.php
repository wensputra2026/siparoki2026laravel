<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WilayahResource\Pages;
use App\Models\Wilayah;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class WilayahResource extends Resource
{
    protected static ?string $model = Wilayah::class;

    protected static ?string $navigationIcon = 'heroicon-o-globe-alt';

    protected static ?string $navigationGroup = 'Wilayah & Referensi';

    protected static ?string $navigationLabel = 'Wilayah Sipil';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'Wilayah';

    protected static ?string $pluralModelLabel = 'Wilayah Rohani';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('kode_wilayah')
                    ->label('Kode Wilayah')
                    ->maxLength(50),
                Forms\Components\TextInput::make('nama_wilayah')
                    ->label('Nama Wilayah')
                    ->required()
                    ->maxLength(150),
                Forms\Components\TextInput::make('ketua_wilayah')
                    ->label('Ketua Wilayah')
                    ->maxLength(150),
                Forms\Components\TextInput::make('no_hp')
                    ->label('No HP / WhatsApp')
                    ->maxLength(50),
                Forms\Components\Textarea::make('deskripsi')
                    ->label('Deskripsi Wilayah')
                    ->rows(3),
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
                Tables\Columns\TextColumn::make('kode_wilayah')
                    ->label('Kode')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('nama_wilayah')
                    ->label('Nama Wilayah')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('ketua_wilayah')
                    ->label('Ketua')
                    ->searchable(),
                Tables\Columns\TextColumn::make('no_hp')
                    ->label('HP / WA'),
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
            ->paginated([15, 25, 50]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWilayahs::route('/'),
            'create' => Pages\CreateWilayah::route('/create'),
            'edit' => Pages\EditWilayah::route('/{record}/edit'),
        ];
    }
}
