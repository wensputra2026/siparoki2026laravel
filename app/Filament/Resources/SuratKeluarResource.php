<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SuratKeluarResource\Pages;
use App\Models\SuratKeluar;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SuratKeluarResource extends Resource
{
    protected static ?string $model = SuratKeluar::class;

    protected static ?string $navigationIcon = 'heroicon-o-paper-airplane';

    protected static ?string $navigationGroup = 'Sekretariat Paroki';

    protected static ?string $navigationLabel = 'Surat Keluar';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'Surat Keluar';

    protected static ?string $pluralModelLabel = 'Surat Keluar';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('nomor_surat')
                ->label('Nomor Surat')
                ->required()
                ->maxLength(100)
                ->unique(ignoreRecord: true),
            Forms\Components\DatePicker::make('tanggal_surat')
                ->label('Tanggal Surat')
                ->required(),
            Forms\Components\TextInput::make('tujuan')
                ->label('Tujuan')
                ->required()
                ->maxLength(255),
            Forms\Components\TextInput::make('perihal')
                ->label('Perihal')
                ->required()
                ->maxLength(255),
            Forms\Components\TextInput::make('lampiran')
                ->label('Lampiran')
                ->maxLength(255),
            Forms\Components\TextInput::make('penandatangan')
                ->label('Penandatangan')
                ->maxLength(255),
            Forms\Components\Select::make('status_draft')
                ->label('Status Draft')
                ->options([
                    'Draft' => 'Draft',
                    'Disetujui' => 'Disetujui',
                    'Dikirim' => 'Dikirim',
                    'Ditolak' => 'Ditolak',
                ])
                ->default('Draft'),
            Forms\Components\Textarea::make('keterangan')
                ->label('Keterangan')
                ->rows(3),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nomor_surat')
                    ->label('No. Surat')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tanggal_surat')
                    ->label('Tgl Surat')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('tujuan')
                    ->label('Tujuan')
                    ->searchable()
                    ->limit(30),
                Tables\Columns\TextColumn::make('perihal')
                    ->label('Perihal')
                    ->searchable()
                    ->limit(40),
                Tables\Columns\TextColumn::make('penandatangan')
                    ->label('Penandatangan')
                    ->searchable()
                    ->limit(30),
                Tables\Columns\TextColumn::make('status_draft')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Draft' => 'gray',
                        'Disetujui' => 'info',
                        'Dikirim' => 'success',
                        'Ditolak' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status_draft')
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
            'index' => Pages\ListSuratKeluars::route('/'),
            'create' => Pages\CreateSuratKeluar::route('/create'),
            'edit' => Pages\EditSuratKeluar::route('/{record}/edit'),
        ];
    }
}
