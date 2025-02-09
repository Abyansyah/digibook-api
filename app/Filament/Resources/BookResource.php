<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BookResource\Pages;
use App\Filament\Resources\BookResource\RelationManagers\BookcategoriesRelationManager;
use App\Filament\Resources\BookResource\RelationManagers\BookcategoryRelationManager;
use App\Filament\Resources\BookResource\RelationManagers\CategoriesRelationManager;
use App\Models\Book;
use App\Models\BookCategory;
use Faker\Provider\ar_EG\Text;
use Filament\Forms;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;
use Pelmered\FilamentMoneyField\Forms\Components\MoneyInput;
use Illuminate\Support\Str;
use Joaopaulolndev\FilamentPdfViewer\Infolists\Components\PdfViewerEntry;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Smalot\PdfParser\Parser;

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
                                    ->live(onBlur: true)
                                    ->unique(ignoreRecord: true)
                                    ->label('Judul Buku')
                                    ->afterStateUpdated(function (string $operation, $state, Set $set) {
                                        if ($operation !== 'create') {
                                            return;
                                        }

                                        $set('slug', Str::slug($state));
                                    }),
                                TextInput::make('slug')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->dehydrated()
                                    ->disabled(),
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
                                Select::make('language')
                                    ->options([
                                        'Bahasa Indonesia' => 'Bahasa Indonesia',
                                        'English' => 'English',
                                    ])
                                    ->required()
                                    ->label('Bahasa'),
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
                                Select::make('categories')
                                    ->relationship('categories', 'name')
                                    ->multiple()
                                    ->searchable()
                                    ->label('Pilih Kategori')
                                    ->required(),
                                Select::make('library_id')
                                    ->relationship('library', 'library_name')
                                    ->label('Pilih Perpustakaan'),
                                TextInput::make('publisher')
                                    ->required()
                                    ->label('Penerbit'),
                                Select::make('publication_year')
                                    ->options(array_combine(range(now()->year, 1900), range(now()->year, 1900)))
                                    ->required()
                                    ->hidden(fn(Get $get): bool => $get('publication_year') === false)
                                    ->label('Tahun Terbit')
                                    ->searchable(),
                                TextInput::make('page_count')
                                    ->numeric()
                                    ->required()
                                    ->label('Jumlah Halaman'),
                            ])->columns(2)
                    ]),


                Card::make()
                    ->schema([
                        FileUpload::make('book_file')
                            ->acceptedFileTypes(['application/pdf'])
                            ->required()
                            ->directory('book-files')
                            ->preserveFilenames()
                            ->previewable(true)
                            ->openable()
                            ->getUploadedFileNameForStorageUsing(function (TemporaryUploadedFile $file): string {
                                return (string) str($file->getClientOriginalName())->prepend(now()->timestamp);
                            })
                            ->afterStateUpdated(function ($state, Set $set) {
                                if ($state) {
                                    try {
                                        $parser = new Parser();
                                        $pdf = $parser->parseFile($state->getRealPath());
                                        $set('page_count', count($pdf->getPages()));
                                    } catch (\Exception $e) {
                                        $set('page_count', 0);
                                    }
                                }
                            })
                            ->label('File Buku'),
                    ]),

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
                TextColumn::make('categories')
                    ->label('Kategori')
                    ->formatStateUsing(fn($record) => $record->categories->pluck('category_name')->join(', '))
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
            // CategoriesRelationManager::class
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
