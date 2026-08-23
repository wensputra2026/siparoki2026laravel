<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SecuritySettingsResource\Pages;
use App\Models\SecuritySettings;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SecuritySettingsResource extends Resource
{
    protected static ?string $model = SecuritySettings::class;

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';

    protected static ?string $navigationGroup = 'Sistem & Aplikasi';

    protected static ?string $navigationLabel = 'Security Center';

    protected static ?int $navigationSort = 4;

    protected static ?string $modelLabel = 'Security Setting';

    protected static ?string $pluralModelLabel = 'Security Settings';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('setting_key')
                ->label('Setting Key')
                ->required()
                ->maxLength(255)
                ->unique(ignoreRecord: true),
            Forms\Components\Textarea::make('setting_value')
                ->label('Setting Value')
                ->rows(5)
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('setting_key')
                    ->label('Setting Key')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('setting_value')
                    ->label('Setting Value')
                    ->limit(80),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diubah')
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
            ->defaultSort('setting_key', 'asc')
            ->paginated([15, 25, 50]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSecuritySettings::route('/'),
            'create' => Pages\CreateSecuritySetting::route('/create'),
            'edit' => Pages\EditSecuritySetting::route('/{record}/edit'),
        ];
    }
}
