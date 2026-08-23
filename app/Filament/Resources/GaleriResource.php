<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GaleriResource\Pages;
use App\Models\Galeri;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class GaleriResource extends Resource
{
    protected static ?string $model = Galeri::class;

    protected static ?string $navigationIcon = 'heroicon-o-photo';

    protected static ?string $navigationGroup = 'Website Paroki';

    protected static ?string $navigationLabel = 'Galeri Foto';

    protected static ?int $navigationSort = 3;

    protected static ?string $modelLabel = 'Galeri';

    protected static ?string $pluralModelLabel = 'Galeri Foto & Video';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('judul')
                    ->label('Judul Kegiatan')
                    ->required()
                    ->maxLength(300),
                Forms\Components\TextInput::make('slug')
                    ->label('Slug URL')
                    ->required()
                    ->maxLength(300)
                    ->unique(ignoreRecord: true),
                Forms\Components\Textarea::make('deskripsi')
                    ->label('Deskripsi Kegiatan')
                    ->rows(3),
                Forms\Components\Select::make('tipe')
                    ->label('Tipe Media')
                    ->options([
                        'Foto' => 'Foto',
                        'Video' => 'Video',
                    ])
                    ->default('Foto'),
                Forms\Components\TextInput::make('album')
                    ->label('Nama Album')
                    ->placeholder('Contoh: Paskah 2026, Krisma 2026')
                    ->maxLength(200),
                Forms\Components\DatePicker::make('tanggal')
                    ->label('Tanggal Kegiatan'),
                Forms\Components\FileUpload::make('gambar')
                    ->label('File Foto / Banner')
                    ->image()
                    ->directory('galeri'),
                Forms\Components\TextInput::make('youtube_url')
                    ->label('YouTube URL (Jika Video)')
                    ->maxLength(255),
                Forms\Components\Toggle::make('status')
                    ->label('Status Aktif')
                    ->default(true),
            ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('gambar')
                    ->label('Foto')
                    ->circular(),
                Tables\Columns\TextColumn::make('judul')
                    ->label('Judul')
                    ->searchable()
                    ->sortable()
                    ->limit(40),
                Tables\Columns\TextColumn::make('tipe')
                    ->label('Tipe')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Foto' => 'info',
                        'Video' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('album')
                    ->label('Album')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tanggal')
                    ->label('Tanggal')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\IconColumn::make('status')
                    ->label('Aktif')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('tipe')
                    ->label('Tipe Media')
                    ->options([
                        'Foto' => 'Foto',
                        'Video' => 'Video',
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
            'index' => Pages\ListGaleris::route('/'),
            'create' => Pages\CreateGaleri::route('/create'),
            'edit' => Pages\EditGaleri::route('/{record}/edit'),
        ];
    }
}
