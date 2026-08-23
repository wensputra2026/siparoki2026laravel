<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LingkunganResource\Pages;
use App\Models\Lingkungan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class LingkunganResource extends Resource
{
    protected static ?string $model = Lingkungan::class;

    protected static ?string $navigationIcon = 'heroicon-o-map';

    protected static ?string $navigationGroup = 'Wilayah & Referensi';

    protected static ?string $navigationLabel = 'Data Referensi';

    protected static ?int $navigationSort = 3;

    protected static ?string $modelLabel = 'Lingkungan / KUB';

    protected static ?string $pluralModelLabel = 'Lingkungan / KUB';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('wilayah_id')
                    ->label('Wilayah')
                    ->relationship('wilayah', 'nama_wilayah')
                    ->searchable()
                    ->preload(),
                Forms\Components\Select::make('kapela_id')
                    ->label('Kapela / Stasi')
                    ->relationship('kapela', 'nama_kapela')
                    ->searchable()
                    ->preload(),
                Forms\Components\TextInput::make('kode_lingkungan')
                    ->label('Kode Lingkungan')
                    ->required()
                    ->maxLength(20),
                Forms\Components\TextInput::make('nama_lingkungan')
                    ->label('Nama Lingkungan / KUB')
                    ->required()
                    ->maxLength(200),
                Forms\Components\TextInput::make('ketua_lingkungan')
                    ->label('Ketua Lingkungan')
                    ->maxLength(200),
                Forms\Components\TextInput::make('no_hp')
                    ->label('No HP / WA')
                    ->maxLength(20),
                Forms\Components\Textarea::make('keterangan')
                    ->label('Keterangan')
                    ->rows(3)
                    ->columnSpanFull(),
                Forms\Components\Toggle::make('status')
                    ->label('Status Aktif')
                    ->default(true),
            ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('kode_lingkungan')
                    ->label('Kode')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('nama_lingkungan')
                    ->label('Nama Lingkungan')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('wilayah.nama_wilayah')
                    ->label('Wilayah')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('ketua_lingkungan')
                    ->label('Ketua')
                    ->searchable(),
                Tables\Columns\TextColumn::make('no_hp')
                    ->label('HP / WA'),
                Tables\Columns\IconColumn::make('status')
                    ->label('Aktif')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('wilayah_id')
                    ->label('Wilayah')
                    ->relationship('wilayah', 'nama_wilayah'),
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
            'index' => Pages\ListLingkungans::route('/'),
            'create' => Pages\CreateLingkungan::route('/create'),
            'edit' => Pages\EditLingkungan::route('/{record}/edit'),
        ];
    }
}
