<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KeuanganResource\Pages;
use App\Models\Keuangan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class KeuanganResource extends Resource
{
    protected static ?string $model = Keuangan::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationGroup = 'Keuangan & Aset';

    protected static ?string $navigationLabel = 'Keuangan & Iuran';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'Keuangan';

    protected static ?string $pluralModelLabel = 'Keuangan & Iuran';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Transaksi Keuangan')
                    ->schema([
                        Forms\Components\DatePicker::make('tanggal')
                            ->label('Tanggal Transaksi')
                            ->required(),
                        Forms\Components\Select::make('jenis')
                            ->label('Jenis Transaksi')
                            ->options([
                                'pemasukan' => 'Pemasukan (Kolekte / Iuran / Donasi)',
                                'pengeluaran' => 'Pengeluaran (Operasional / Kegiatan)',
                            ])
                            ->required(),
                        Forms\Components\TextInput::make('kategori')
                            ->label('Kategori')
                            ->placeholder('Contoh: Kolekte I, Iuran Pembangunan, Operasional')
                            ->required()
                            ->maxLength(100),
                        Forms\Components\TextInput::make('kode_coa')
                            ->label('Kode Akun / COA')
                            ->maxLength(20),
                        Forms\Components\TextInput::make('jumlah')
                            ->label('Jumlah Nominal')
                            ->required()
                            ->numeric()
                            ->prefix('Rp'),
                        Forms\Components\TextInput::make('penerima')
                            ->label('Penerima / Sumber Dana')
                            ->maxLength(150),
                        Forms\Components\Select::make('status')
                            ->label('Status Approval')
                            ->options([
                                'approved' => 'Approved / Disetujui',
                                'pending' => 'Pending',
                                'rejected' => 'Rejected',
                            ])
                            ->default('approved'),
                        Forms\Components\Textarea::make('keterangan')
                            ->label('Keterangan / Uraian')
                            ->required()
                            ->rows(3)
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tanggal')
                    ->label('Tanggal')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('jenis')
                    ->label('Jenis')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pemasukan' => 'success',
                        'pengeluaran' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('kategori')
                    ->label('Kategori')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('jumlah')
                    ->label('Jumlah')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('penerima')
                    ->label('Penerima / Sumber')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'approved' => 'success',
                        'pending' => 'warning',
                        'rejected' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('jenis')
                    ->label('Jenis Transaksi')
                    ->options([
                        'pemasukan' => 'Pemasukan',
                        'pengeluaran' => 'Pengeluaran',
                    ]),
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'approved' => 'Approved',
                        'pending' => 'Pending',
                        'rejected' => 'Rejected',
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
            'index' => Pages\ListKeuangans::route('/'),
            'create' => Pages\CreateKeuangan::route('/create'),
            'edit' => Pages\EditKeuangan::route('/{record}/edit'),
        ];
    }
}
