<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SuratMasukResource\Pages;
use App\Models\SuratMasuk;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SuratMasukResource extends Resource
{
    protected static ?string $model = SuratMasuk::class;

    protected static ?string $navigationIcon = 'heroicon-o-inbox';

    protected static ?string $navigationGroup = 'Sekretariat Paroki';

    protected static ?string $navigationLabel = 'Surat Masuk';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'Surat Masuk';

    protected static ?string $pluralModelLabel = 'Surat Masuk';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('nomor_agenda')
                ->label('Nomor Agenda')
                ->required()
                ->maxLength(100)
                ->unique(ignoreRecord: true),
            Forms\Components\TextInput::make('nomor_surat')
                ->label('Nomor Surat')
                ->required()
                ->maxLength(100),
            Forms\Components\DatePicker::make('tanggal_surat')
                ->label('Tanggal Surat')
                ->required(),
            Forms\Components\DatePicker::make('tanggal_diterima')
                ->label('Tanggal Diterima')
                ->required(),
            Forms\Components\TextInput::make('pengirim')
                ->label('Pengirim')
                ->required()
                ->maxLength(255),
            Forms\Components\TextInput::make('perihal')
                ->label('Perihal')
                ->required()
                ->maxLength(255),
            Forms\Components\TextInput::make('lampiran')
                ->label('Lampiran')
                ->maxLength(255),
            Forms\Components\Select::make('status')
                ->label('Status')
                ->options([
                    'Baru' => 'Baru',
                    'Diproses' => 'Diproses',
                    'Selesai' => 'Selesai',
                    'Ditolak' => 'Ditolak',
                ])
                ->default('Baru'),
            Forms\Components\Textarea::make('keterangan')
                ->label('Keterangan')
                ->rows(3),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nomor_agenda')
                    ->label('No. Agenda')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('nomor_surat')
                    ->label('No. Surat')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tanggal_surat')
                    ->label('Tgl Surat')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('pengirim')
                    ->label('Pengirim')
                    ->searchable()
                    ->limit(30),
                Tables\Columns\TextColumn::make('perihal')
                    ->label('Perihal')
                    ->searchable()
                    ->limit(40),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Baru' => 'info',
                        'Diproses' => 'warning',
                        'Selesai' => 'success',
                        'Ditolak' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('tanggal_diterima')
                    ->label('Tgl Diterima')
                    ->date('d/m/Y')
                    ->sortable(),
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
            'index' => Pages\ListSuratMasuks::route('/'),
            'create' => Pages\CreateSuratMasuk::route('/create'),
            'edit' => Pages\EditSuratMasuk::route('/{record}/edit'),
        ];
    }
}
