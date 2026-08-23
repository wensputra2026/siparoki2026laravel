<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PengumumanResource\Pages;
use App\Models\Pengumuman;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PengumumanResource extends Resource
{
    protected static ?string $model = Pengumuman::class;

    protected static ?string $navigationIcon = 'heroicon-o-megaphone';

    protected static ?string $navigationGroup = 'Website Paroki';

    protected static ?string $navigationLabel = 'Pengumuman';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'Pengumuman';

    protected static ?string $pluralModelLabel = 'Pengumuman';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('judul')
                    ->label('Judul')
                    ->required()
                    ->maxLength(300),
                Forms\Components\TextInput::make('slug')
                    ->label('Slug')
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                Forms\Components\Textarea::make('ringkasan')
                    ->label('Ringkasan')
                    ->rows(3),
                Forms\Components\RichEditor::make('isi')
                    ->label('Isi Pengumuman')
                    ->columnSpanFull(),
                Forms\Components\DatePicker::make('tgl_mulai')
                    ->label('Tanggal Mulai'),
                Forms\Components\DatePicker::make('tgl_selesai')
                    ->label('Tanggal Selesai'),
                Forms\Components\Select::make('kategori')
                    ->label('Kategori')
                    ->options([
                        'PENGUMUMAN' => 'Pengumuman',
                        'JADWAL' => 'Jadwal',
                        'ACARA' => 'Acara',
                    ])
                    ->default('PENGUMUMAN'),
                Forms\Components\Select::make('status')
                    ->label('Status')
                    ->options([
                        1 => 'Aktif',
                        0 => 'Tidak Aktif',
                    ])
                    ->default(1),
            ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('judul')
                    ->label('Judul Pengumuman')
                    ->searchable()
                    ->sortable()
                    ->limit(50),
                Tables\Columns\TextColumn::make('kategori')
                    ->label('Kategori')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'PENGUMUMAN' => 'info',
                        'JADWAL' => 'success',
                        'ACARA' => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('tgl_mulai')
                    ->label('Mulai')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('tgl_selesai')
                    ->label('Selesai')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\IconColumn::make('status')
                    ->label('Aktif')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('kategori')
                    ->label('Kategori')
                    ->options([
                        'PENGUMUMAN' => 'Pengumuman',
                        'JADWAL' => 'Jadwal',
                        'ACARA' => 'Acara',
                    ]),
                Tables\Filters\TernaryFilter::make('status')
                    ->label('Status Aktif'),
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
            'index' => Pages\ListPengumumans::route('/'),
            'create' => Pages\CreatePengumuman::route('/create'),
            'edit' => Pages\EditPengumuman::route('/{record}/edit'),
        ];
    }
}
