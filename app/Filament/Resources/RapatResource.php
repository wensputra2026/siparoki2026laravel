<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RapatResource\Pages;
use App\Models\Rapat;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class RapatResource extends Resource
{
    protected static ?string $model = Rapat::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'Sekretariat Paroki';

    protected static ?string $navigationLabel = 'Rapat & Notulen';

    protected static ?int $navigationSort = 4;

    protected static ?string $modelLabel = 'Rapat';

    protected static ?string $pluralModelLabel = 'Rapat & Notulen';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Textarea::make('agenda')
                ->label('Agenda')
                ->required()
                ->rows(3),
            Forms\Components\DatePicker::make('tanggal')
                ->label('Tanggal')
                ->required(),
            Forms\Components\TextInput::make('waktu')
                ->label('Waktu')
                ->maxLength(100)
                ->placeholder('Contoh: 09:00 - 12:00'),
            Forms\Components\TextInput::make('lokasi')
                ->label('Lokasi')
                ->maxLength(255),
            Forms\Components\Textarea::make('notulen')
                ->label('Notulensi')
                ->rows(5),
            Forms\Components\Select::make('status')
                ->label('Status')
                ->options([
                    'Dijadwalkan' => 'Dijadwalkan',
                    'Berlangsung' => 'Berlangsung',
                    'Selesai' => 'Selesai',
                    'Dibatalkan' => 'Dibatalkan',
                ])
                ->default('Dijadwalkan'),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('agenda')
                    ->label('Agenda')
                    ->searchable()
                    ->limit(50),
                Tables\Columns\TextColumn::make('tanggal')
                    ->label('Tanggal')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('waktu')
                    ->label('Waktu'),
                Tables\Columns\TextColumn::make('lokasi')
                    ->label('Lokasi')
                    ->searchable()
                    ->limit(30),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Dijadwalkan' => 'info',
                        'Berlangsung' => 'warning',
                        'Selesai' => 'success',
                        'Dibatalkan' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status'),
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
            ->defaultSort('tanggal', 'desc')
            ->paginated([15, 25, 50]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRapats::route('/'),
            'create' => Pages\CreateRapat::route('/create'),
            'edit' => Pages\EditRapat::route('/{record}/edit'),
        ];
    }
}
