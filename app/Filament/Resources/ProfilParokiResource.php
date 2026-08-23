<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProfilParokiResource\Pages;
use App\Models\ProfilParoki;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ProfilParokiResource extends Resource
{
    protected static ?string $model = ProfilParoki::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?string $navigationGroup = 'Sistem & Aplikasi';

    protected static ?string $navigationLabel = 'Profil Paroki';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'Profil Paroki';

    protected static ?string $pluralModelLabel = 'Profil Paroki';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('nama_paroki')
                ->label('Nama Paroki')
                ->required()
                ->maxLength(255),
            Forms\Components\TextInput::make('keuskupan')
                ->label('Keuskupan')
                ->maxLength(255),
            Forms\Components\Textarea::make('alamat')
                ->label('Alamat')
                ->rows(3),
            Forms\Components\TextInput::make('telepon')
                ->label('Telepon')
                ->maxLength(50),
            Forms\Components\TextInput::make('email')
                ->label('Email')
                ->email()
                ->maxLength(255),
            Forms\Components\TextInput::make('website')
                ->label('Website')
                ->url()
                ->maxLength(255),
            Forms\Components\TextInput::make('pastor_paroki')
                ->label('Pastor Paroki')
                ->maxLength(255),
            Forms\Components\TextInput::make('pastor_email')
                ->label('Email Pastor')
                ->email()
                ->maxLength(255),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama_paroki')
                    ->label('Nama Paroki')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('keuskupan')
                    ->label('Keuskupan')
                    ->searchable(),
                Tables\Columns\TextColumn::make('telepon')
                    ->label('Telepon'),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('pastor_paroki')
                    ->label('Pastor Paroki')
                    ->searchable()
                    ->limit(30),
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
            'index' => Pages\ListProfilParokis::route('/'),
            'create' => Pages\CreateProfilParoki::route('/create'),
            'edit' => Pages\EditProfilParoki::route('/{record}/edit'),
        ];
    }
}
