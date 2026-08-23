<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PengaturanAplikasiResource\Pages;
use App\Models\PengaturanAplikasi;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PengaturanAplikasiResource extends Resource
{
    protected static ?string $model = PengaturanAplikasi::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationGroup = 'Sistem & Aplikasi';

    protected static ?string $navigationLabel = 'Pengaturan Aplikasi';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'Pengaturan Aplikasi';

    protected static ?string $pluralModelLabel = 'Pengaturan Aplikasi';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('id_pengaturan')
                ->label('ID Pengaturan')
                ->required()
                ->maxLength(50),
            Forms\Components\TextInput::make('nama_aplikasi')
                ->label('Nama Aplikasi')
                ->required()
                ->maxLength(255),
            Forms\Components\TextInput::make('nama_paroki')
                ->label('Nama Paroki')
                ->maxLength(255),
            Forms\Components\Textarea::make('alamat_paroki')
                ->label('Alamat Paroki')
                ->rows(3),
            Forms\Components\TextInput::make('telepon_paroki')
                ->label('Telepon Paroki')
                ->maxLength(50),
            Forms\Components\TextInput::make('email_paroki')
                ->label('Email Paroki')
                ->email()
                ->maxLength(255),
            Forms\Components\TextInput::make('logo')
                ->label('Logo Path')
                ->maxLength(500),
            Forms\Components\TextInput::make('favicon')
                ->label('Favicon Path')
                ->maxLength(500),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id_pengaturan')
                    ->label('ID')
                    ->searchable(),
                Tables\Columns\TextColumn::make('nama_aplikasi')
                    ->label('Nama Aplikasi')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('nama_paroki')
                    ->label('Nama Paroki')
                    ->searchable(),
                Tables\Columns\TextColumn::make('telepon_paroki')
                    ->label('Telepon'),
                Tables\Columns\TextColumn::make('email_paroki')
                    ->label('Email')
                    ->searchable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->defaultSort('id', 'asc')
            ->paginated([15, 25, 50]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPengaturanAplikasis::route('/'),
            'create' => Pages\CreatePengaturanAplikasi::route('/create'),
            'edit' => Pages\EditPengaturanAplikasi::route('/{record}/edit'),
        ];
    }
}
