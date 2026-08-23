<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KegiatanResource\Pages;
use App\Models\Kegiatan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class KegiatanResource extends Resource
{
    protected static ?string $model = Kegiatan::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar';

    protected static ?string $navigationGroup = 'Umat & Pelayanan';

    protected static ?string $navigationLabel = 'Agenda Kegiatan';

    protected static ?int $navigationSort = 5;

    protected static ?string $modelLabel = 'Kegiatan';

    protected static ?string $pluralModelLabel = 'Agenda Kegiatan';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('judul')
                ->label('Judul')
                ->required()
                ->maxLength(255),
            Forms\Components\TextInput::make('slug')
                ->label('Slug URL')
                ->required()
                ->maxLength(255)
                ->unique(ignoreRecord: true),
            Forms\Components\Select::make('kategori')
                ->label('Kategori')
                ->options([
                    'Ibadah' => 'Ibadah',
                    'Retret' => 'Retret',
                    'Katekese' => 'Katekese',
                    'Bakti Sosial' => 'Bakti Sosial',
                    'Perayaan' => 'Perayaan',
                    'Pertemuan' => 'Pertemuan',
                    'Lainnya' => 'Lainnya',
                ])
                ->required(),
            Forms\Components\DatePicker::make('tanggal_mulai')
                ->label('Tanggal Mulai')
                ->required(),
            Forms\Components\DatePicker::make('tanggal_selesai')
                ->label('Tanggal Selesai'),
            Forms\Components\TextInput::make('lokasi')
                ->label('Lokasi')
                ->maxLength(255),
            Forms\Components\Select::make('status')
                ->label('Status')
                ->options([
                    'Dijadwalkan' => 'Dijadwalkan',
                    'Berlangsung' => 'Berlangsung',
                    'Selesai' => 'Selesai',
                    'Dibatalkan' => 'Dibatalkan',
                ])
                ->default('Dijadwalkan'),
            Forms\Components\Textarea::make('deskripsi')
                ->label('Deskripsi')
                ->rows(5)
                ->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('judul')
                    ->label('Judul')
                    ->searchable()
                    ->sortable()
                    ->limit(40),
                Tables\Columns\TextColumn::make('kategori')
                    ->label('Kategori')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Ibadah' => 'info',
                        'Retret' => 'primary',
                        'Katekese' => 'success',
                        'Bakti Sosial' => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('tanggal_mulai')
                    ->label('Mulai')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('tanggal_selesai')
                    ->label('Selesai')
                    ->date('d/m/Y'),
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
                Tables\Filters\SelectFilter::make('kategori')
                    ->label('Kategori'),
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
            ->defaultSort('tanggal_mulai', 'desc')
            ->paginated([15, 25, 50]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListKegiatans::route('/'),
            'create' => Pages\CreateKegiatan::route('/create'),
            'edit' => Pages\EditKegiatan::route('/{record}/edit'),
        ];
    }
}
