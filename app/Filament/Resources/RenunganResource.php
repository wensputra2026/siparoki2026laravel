<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RenunganResource\Pages;
use App\Models\Renungan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class RenunganResource extends Resource
{
    protected static ?string $model = Renungan::class;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    protected static ?string $navigationGroup = 'Website Paroki';

    protected static ?string $navigationLabel = 'Renungan';

    protected static ?int $navigationSort = 5;

    protected static ?string $modelLabel = 'Renungan';

    protected static ?string $pluralModelLabel = 'Renungan Harian';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\DatePicker::make('tanggal')
                ->label('Tanggal')
                ->required(),
            Forms\Components\TextInput::make('judul')
                ->label('Judul')
                ->required()
                ->maxLength(255),
            Forms\Components\TextInput::make('slug')
                ->label('Slug URL')
                ->required()
                ->maxLength(255)
                ->unique(ignoreRecord: true),
            Forms\Components\RichEditor::make('isi_renungan')
                ->label('Isi Renungan')
                ->required()
                ->columnSpanFull(),
            Forms\Components\Select::make('status')
                ->label('Status')
                ->options([
                    'Draft' => 'Draft',
                    'Publish' => 'Publish',
                ])
                ->default('Draft'),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tanggal')
                    ->label('Tanggal')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('judul')
                    ->label('Judul')
                    ->searchable()
                    ->sortable()
                    ->limit(50),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Draft' => 'gray',
                        'Publish' => 'success',
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
            'index' => Pages\ListRenungans::route('/'),
            'create' => Pages\CreateRenungan::route('/create'),
            'edit' => Pages\EditRenungan::route('/{record}/edit'),
        ];
    }
}
