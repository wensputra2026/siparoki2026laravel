<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KapelaResource\Pages;
use App\Models\Kapela;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class KapelaResource extends Resource
{
    protected static ?string $model = Kapela::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office';

    protected static ?string $navigationGroup = 'Wilayah & Referensi';

    protected static ?string $navigationLabel = 'Kapela';

    protected static ?int $navigationSort = 4;

    protected static ?string $modelLabel = 'Kapela';

    protected static ?string $pluralModelLabel = 'Kapela / Stasi';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('kode_kapela')
                    ->label('Kode Kapela')
                    ->required()
                    ->maxLength(20)
                    ->unique(ignoreRecord: true),
                Forms\Components\TextInput::make('nama_kapela')
                    ->label('Nama Kapela / Stasi')
                    ->required()
                    ->maxLength(200),
                Forms\Components\TextInput::make('penanggung_jawab')
                    ->label('Penanggung Jawab / Pengurus')
                    ->maxLength(200),
                Forms\Components\TextInput::make('no_hp')
                    ->label('No HP / WA')
                    ->maxLength(20),
                Forms\Components\Textarea::make('lokasi')
                    ->label('Lokasi / Alamat')
                    ->rows(3),
                Forms\Components\Textarea::make('keterangan')
                    ->label('Keterangan')
                    ->rows(3),
                Forms\Components\Toggle::make('status')
                    ->label('Status Aktif')
                    ->default(true),
            ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('kode_kapela')
                    ->label('Kode')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('nama_kapela')
                    ->label('Nama Kapela / Stasi')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('penanggung_jawab')
                    ->label('Penanggung Jawab')
                    ->searchable(),
                Tables\Columns\TextColumn::make('no_hp')
                    ->label('HP / WA'),
                Tables\Columns\IconColumn::make('status')
                    ->label('Aktif')
                    ->boolean(),
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
            'index' => Pages\ListKapelas::route('/'),
            'create' => Pages\CreateKapela::route('/create'),
            'edit' => Pages\EditKapela::route('/{record}/edit'),
        ];
    }
}
