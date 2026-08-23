<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ParokiResource\Pages;
use App\Models\Paroki;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ParokiResource extends Resource
{
    protected static ?string $model = Paroki::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-library';

    protected static ?string $navigationGroup = 'Wilayah & Referensi';

    protected static ?string $navigationLabel = 'Data Gerejawi';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'Paroki';

    protected static ?string $pluralModelLabel = 'Profil Paroki';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Data Paroki')
                    ->schema([
                        Forms\Components\TextInput::make('kode_paroki')
                            ->label('Kode Paroki')
                            ->maxLength(50),
                        Forms\Components\TextInput::make('nama_paroki')
                            ->label('Nama Paroki')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('pelindung_paroki')
                            ->label('Pelindung Paroki')
                            ->maxLength(255),
                        Forms\Components\Select::make('status_paroki')
                            ->label('Status')
                            ->options([
                                'Paroki' => 'Paroki',
                                'Kuasi Paroki' => 'Kuasi Paroki',
                            ])
                            ->default('Paroki'),
                        Forms\Components\DatePicker::make('tanggal_berdiri')
                            ->label('Tanggal Berdiri'),
                    ])->columns(2),

                Forms\Components\Section::make('Pastor')
                    ->schema([
                        Forms\Components\TextInput::make('nama_pastor_paroki_aktif')
                            ->label('Pastor Paroki')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('nama_pastor_rekan')
                            ->label('Pastor Rekan')
                            ->maxLength(255),
                    ])->columns(2),

                Forms\Components\Section::make('Kontak')
                    ->schema([
                        Forms\Components\Textarea::make('alamat')
                            ->label('Alamat')
                            ->rows(3),
                        Forms\Components\TextInput::make('telepon')
                            ->label('Telepon')
                            ->maxLength(50),
                        Forms\Components\TextInput::make('whatsapp')
                            ->label('WhatsApp')
                            ->maxLength(50),
                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->maxLength(150),
                        Forms\Components\TextInput::make('website')
                            ->label('Website')
                            ->maxLength(255),
                    ])->columns(2),

                Forms\Components\Section::make('Lokasi')
                    ->schema([
                        Forms\Components\TextInput::make('latitude')
                            ->label('Latitude')
                            ->maxLength(50),
                        Forms\Components\TextInput::make('longitude')
                            ->label('Longitude')
                            ->maxLength(50),
                        Forms\Components\TextInput::make('maps_url')
                            ->label('Maps URL')
                            ->maxLength(255),
                    ])->columns(3),

                Forms\Components\Section::make('Status')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'Aktif' => 'Aktif',
                                'Tidak Aktif' => 'Tidak Aktif',
                            ])
                            ->default('Aktif'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('kode_paroki')
                    ->label('Kode')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('nama_paroki')
                    ->label('Nama Paroki')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('pelindung_paroki')
                    ->label('Pelindung')
                    ->searchable(),
                Tables\Columns\TextColumn::make('nama_pastor_paroki_aktif')
                    ->label('Pastor Paroki')
                    ->searchable(),
                Tables\Columns\TextColumn::make('telepon')
                    ->label('Telp / WA'),
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
            'index' => Pages\ListParokis::route('/'),
            'create' => Pages\CreateParoki::route('/create'),
            'edit' => Pages\EditParoki::route('/{record}/edit'),
        ];
    }
}
