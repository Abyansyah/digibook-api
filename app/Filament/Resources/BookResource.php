<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BookResource\Pages;
use App\Models\Book;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Pelmered\FilamentMoneyField\Forms\Components\MoneyInput;

class BookResource extends Resource
{
    protected static ?string $model = Book::class;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    protected static ?string $navigationGroup = 'Library';

    protected static ?string $navigationLabel = 'Management Buku';

    public static function form(Form $form): Form
    {

        return $form
            ->schema([
                Group::make()
                    ->schema([
                        Section::make('Book Details')
                            ->schema([
                                TextInput::make('title')
                                    ->required()
                                    ->label('Judul Buku'),
                                TextInput::make('author')
                                    ->required()
                                    ->label('Penulis'),
                                MarkdownEditor::make('description')
                                    ->fileAttachmentsDisk('public')
                                    ->fileAttachmentsDirectory('attachments')
                                    ->fileAttachmentsVisibility('private')
                                    ->columnSpan('full')
                                    ->required()
                                    ->label('Deskripsi'),
                            ])->columns(2),

                        Section::make('Pricing & Stock')
                            ->schema([
                                TextInput::make('isbn')
                                    ->required()
                                    ->label('ISBN'),
                                MoneyInput::make('price')
                                    ->currency('IDR')
                                    ->locale('id_ID')
                                    ->required()
                                    ->label('Harga'),
                                TextInput::make('stock')
                                    ->required()
                                    ->label('Stok'),
                            ])->columns(2),
                    ]),

                Group::make()
                    ->schema([
                        Section::make('Status')
                            ->schema([
                                Toggle::make('is_visible')
                                    ->label('Hide Buku')
                                    ->required()
                                    ->helperText('Buku akan muncul di halaman utama')
                                    ->default(true)
                            ]),

                        Section::make('Image')
                            ->schema([
                                FileUpload::make('image')
                                    ->acceptedFileTypes(['image/png', 'image/jpeg', 'image/jpg'])
                                    ->required()
                                    ->directory('image-books')
                                    ->preserveFilenames()
                                    ->image()
                                    ->imageEditor()
                            ])->collapsible(),

                        Section::make('Category & Library')
                            ->schema([
                                Select::make('category_id')
                                    ->relationship('category', 'category_name')
                                    ->required()
                                    ->label('Pilih Kategori'),
                                Select::make('library_id')
                                    ->relationship('library', 'library_name')
                                    ->label('Pilih Perpustakaan')
                            ])->columns(2)
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')->circular(),
                TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('author'),
                TextColumn::make('isbn'),
                TextColumn::make('stock'),
                TextColumn::make('price'),
                TextColumn::make('category.category_name')
                    ->searchable()
                    ->toggleable(),
                IconColumn::make('is_visible')->label('Visible')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
            ])
            ->filters([
                TernaryFilter::make('is_visible')
                    ->label('Visibility')
                    ->boolean()
                    ->trueLabel('Visible')
                    ->falseLabel('Not Visible')
                    ->native(false),

                SelectFilter::make('category')
                    ->relationship('category', 'category_name')
            ])
            ->actions([
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBooks::route('/'),
            'create' => Pages\CreateBook::route('/create'),
            'edit' => Pages\EditBook::route('/{record}/edit'),
        ];
    }
}
