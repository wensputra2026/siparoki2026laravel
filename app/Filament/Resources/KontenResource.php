<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KontenResource\Pages;
use App\Models\Konten;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class KontenResource extends Resource
{
    protected static ?string $model = Konten::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Website Paroki';

    protected static ?string $navigationLabel = 'Konten Website';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'Konten';

    protected static ?string $pluralModelLabel = 'Berita & Konten';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Data Konten')
                    ->schema([
                        Forms\Components\Select::make('tipe')
                            ->label('Tipe Konten')
                            ->options([
                                'Berita' => 'Berita',
                                'Artikel' => 'Artikel',
                                'Renungan' => 'Renungan',
                                'Pengumuman' => 'Pengumuman',
                                'Halaman' => 'Halaman',
                            ])
                            ->required()
                            ->default('Artikel'),
                        Forms\Components\TextInput::make('judul')
                            ->label('Judul')
                            ->required()
                            ->maxLength(500),
                        Forms\Components\TextInput::make('slug')
                            ->label('Slug URL')
                            ->required()
                            ->maxLength(500)
                            ->unique(ignoreRecord: true),
                        Forms\Components\Textarea::make('excerpt')
                            ->label('Ringkasan / Cuplikan')
                            ->rows(3),
                        Forms\Components\RichEditor::make('isi')
                            ->label('Isi Konten Lengkap')
                            ->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make('Media & Publikasi')
                    ->schema([
                        Forms\Components\FileUpload::make('gambar')
                            ->label('Gambar Utama')
                            ->image()
                            ->directory('konten'),
                        Forms\Components\Select::make('status_publish')
                            ->label('Status')
                            ->options([
                                'Draft' => 'Draft',
                                'Publish' => 'Publish',
                            ])
                            ->default('Draft'),
                        Forms\Components\DateTimePicker::make('tanggal_publish')
                            ->label('Tanggal Publikasi'),
                        Forms\Components\Toggle::make('is_featured')
                            ->label('Tampilkan di Banner Utama (Featured)')
                            ->default(false),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('gambar')
                    ->label('Foto')
                    ->circular(),
                Tables\Columns\TextColumn::make('tipe')
                    ->label('Tipe')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Berita' => 'info',
                        'Artikel' => 'success',
                        'Renungan' => 'warning',
                        'Pengumuman' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('judul')
                    ->label('Judul')
                    ->searchable()
                    ->sortable()
                    ->limit(50),
                Tables\Columns\TextColumn::make('status_publish')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Publish' => 'success',
                        'Draft' => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('tanggal_publish')
                    ->label('Tgl Publish')
                    ->date('d/m/Y H:i')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_featured')
                    ->label('Featured')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('tipe')
                    ->label('Tipe Konten')
                    ->options([
                        'Berita' => 'Berita',
                        'Artikel' => 'Artikel',
                        'Renungan' => 'Renungan',
                        'Pengumuman' => 'Pengumuman',
                        'Halaman' => 'Halaman',
                    ]),
                Tables\Filters\SelectFilter::make('status_publish')
                    ->label('Status Publish')
                    ->options([
                        'Draft' => 'Draft',
                        'Publish' => 'Publish',
                    ]),
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
            'index' => Pages\ListKontens::route('/'),
            'create' => Pages\CreateKonten::route('/create'),
            'edit' => Pages\EditKonten::route('/{record}/edit'),
        ];
    }
}
