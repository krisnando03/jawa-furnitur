<?php

namespace App\Filament\Resources;

use App\Models\Produk;
use App\Filament\Resources\ProdukResource\Pages;
use Filament\Forms;
use Filament\Tables;
use Filament\Resources\Resource;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class ProdukResource extends Resource
{
    protected static ?string $model = Produk::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';
    protected static ?string $navigationLabel = 'Data Produk Furniture';
    protected static ?string $navigationGroup = 'JawaFurnitur';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('id_kategori')
                ->relationship('kategori', 'nama_kategori')
                ->label('Kategori')
                ->required(),

            Forms\Components\TextInput::make('nama_produk')
                ->required()
                ->label('Nama Produk')
                ->live(onBlur: true)
                ->afterStateUpdated(function ($set, $state) {
                    $set('slug', Str::slug($state));
                }),

            Forms\Components\Textarea::make('deskripsi_singkat')
                ->label('Deskripsi Singkat')
                ->maxLength(500),

            Forms\Components\RichEditor::make('deskripsi_lengkap')
                ->label('Deskripsi Lengkap'),

            Forms\Components\TextInput::make('harga')
                ->numeric()
                ->required()
                ->label('Harga'),

            Forms\Components\TextInput::make('stok')
                ->numeric()
                ->required()
                ->label('Stok'),

            Forms\Components\TextInput::make('warna')
                ->label('Warna'),

            Forms\Components\TextInput::make('berat')
                ->numeric()
                ->label('Berat (kg)'),

            Forms\Components\FileUpload::make('gambar_produk')
                ->label('Gambar Produk')
                ->disk('public')
                ->directory('product')
                ->image() // Otomatis mengaktifkan pratinjau gambar dan filter tipe file gambar dasar
                ->imagePreviewHeight('100')
                ->downloadable()
                ->required() // Menjadikan field ini wajib diisi
                ->maxSize(2048) // Batas maksimal ukuran file dalam kilobytes (misalnya, 2MB)
                ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp']) // Lebih spesifik tentang jenis file yang diterima
                ->helperText('Format gambar yang didukung: JPG, PNG, WEBP. Ukuran maksimal: 2MB.'), // Teks bantuan untuk pengguna
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('gambar_produk_url')
                    ->label('Gambar')
                    ->disk('public')
                    ->width(60),

                Tables\Columns\TextColumn::make('nama_produk')->label('Nama Produk')->searchable(),
                Tables\Columns\TextColumn::make('kategori.nama_kategori')->label('Kategori'),
                Tables\Columns\TextColumn::make('harga')->label('Harga')->money('IDR', true),
                Tables\Columns\TextColumn::make('stok')->label('Stok'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('Edit'),
                Tables\Actions\DeleteAction::make()->label('Hapus'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->label('Tambah Produk'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProduk::route('/'),
            'create' => Pages\CreateProduk::route('/create'),
            'edit' => Pages\EditProduk::route('/{record}/edit'),
        ];
    }
}
