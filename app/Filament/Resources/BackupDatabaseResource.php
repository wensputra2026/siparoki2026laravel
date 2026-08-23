<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BackupDatabaseResource\Pages;
use App\Models\BackupDatabase;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class BackupDatabaseResource extends Resource
{
    protected static ?string $model = BackupDatabase::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-up-tray';

    protected static ?string $navigationGroup = 'Sistem & Aplikasi';

    protected static ?string $navigationLabel = 'Backup & Restore';

    protected static ?int $navigationSort = 5;

    protected static ?string $modelLabel = 'Backup';

    protected static ?string $pluralModelLabel = 'Backup Database';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('nama_file')
                ->label('Nama File')
                ->required()
                ->maxLength(255),
            Forms\Components\TextInput::make('ukuran')
                ->label('Ukuran (bytes)')
                ->numeric(),
            Forms\Components\TextInput::make('dibuat_oleh')
                ->label('Dibuat Oleh')
                ->maxLength(255),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama_file')
                    ->label('Nama File')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('ukuran')
                    ->label('Ukuran')
                    ->formatStateUsing(fn ($state): string => $state ? number_format($state / 1024 / 1024, 2) . ' MB' : '-'),
                Tables\Columns\TextColumn::make('dibuat_oleh')
                    ->label('Dibuat Oleh')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal Dibuat')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
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
            'index' => Pages\ListBackupDatabases::route('/'),
            'create' => Pages\CreateBackupDatabase::route('/create'),
            'edit' => Pages\EditBackupDatabase::route('/{record}/edit'),
        ];
    }
}
