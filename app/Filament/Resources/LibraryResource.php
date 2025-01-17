<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LibraryResource\Pages;
use App\Filament\Resources\LibraryResource\RelationManagers;
use App\Models\Library;
use App\Models\User;
use BladeUI\Icons\Components\Icon;
use Filament\Forms;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class LibraryResource extends Resource
{
    protected static ?string $model = Library::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-library';

    protected static ?string $navigationGroup = 'Library';

    protected static ?string $navigationLabel = 'Perpustakaan';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Group::make()
                    ->schema([
                        Section::make('Library Details')
                            ->schema([
                                TextInput::make('library_name')
                                    ->required()
                                    ->maxLength(255)
                                    ->label('Nama Perpustakaan'),
                                TextInput::make('contact_number')
                                    ->required()
                                    ->label('Nomor Telepon')
                                    ->numeric()
                                    ->minValue(0),
                                Textarea::make('address')
                                    ->required()
                                    ->label('Alamat')
                                    ->columnSpan('full')
                                    ->rows(8),
                            ])->columns(2),

                        Section::make('Operasional')
                            ->schema([
                                TimePicker::make('opening_time')
                                    ->required()
                                    ->label('Jam Buka')
                                    ->time()
                                    ->datalist([
                                        '09:00',
                                        '09:30',
                                        '10:00',
                                        '10:30',
                                        '11:00',
                                        '11:30',
                                        '12:00',
                                    ]),
                                TimePicker::make('closing_time')
                                    ->required()
                                    ->label('Jam Tutup')
                                    ->time()
                                    ->datalist([
                                        '13:00',
                                        '13:30',
                                        '14:00',
                                        '14:30',
                                        '15:00',
                                        '15:30',
                                        '16:00',
                                        '16:30',
                                        '17:00',
                                        '17:30',
                                        '18:00',
                                        '18:30',
                                        '19:00',
                                        '19:30',
                                    ]),
                                Select::make('head_library_id')
                                    ->label('Kepala Perpustakaan')
                                    ->options(User::all()->pluck('name', 'id'))
                                    ->searchable()
                                    ->required(),
                            ])->columns(2),

                    ]),

                Group::make()
                    ->schema([

                        Section::make('Pengaturan')
                            ->schema([
                                Toggle::make('is_visible')
                                    ->required()
                                    ->label('Tampilkan Perpustakaan')
                                    ->helperText('Tampilkan perpustakaan di halaman utama')
                                    ->default(true),
                            ]),

                        Section::make('Gambar')
                            ->schema([
                                FileUpload::make('image')
                                    ->label('Gambar Perpustakaan')
                                    ->image()
                                    ->imageEditor()
                                    ->required()
                                    ->acceptedFileTypes(['image/png', 'image/jpeg', 'image/jpg'])
                                    ->directory("libraries")
                                    ->preserveFilenames()
                            ])->collapsible(),
                        Section::make()
                            ->schema([
                                RichEditor::make('description')
                                    ->required()
                                    ->label('Deskripsi Perpustakaan')
                                    ->fileAttachmentsDirectory('attachments')
                            ]),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')->circular(),
                TextColumn::make('library_name')
                    ->label('Perpustakaan')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('contact_number')
                    ->label('Nomor Telepon')
                    ->searchable(),
                IconColumn::make('is_visible')
                    ->label('Visible')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle'),
                TextColumn::make('opening_time')
                    ->label('Jam Buka')
                    ->formatStateUsing(fn($state) => \Carbon\Carbon::parse($state)->format('H:i')),
                TextColumn::make('closing_time')
                    ->label('Jam Tutup')
                    ->formatStateUsing(fn($state) => \Carbon\Carbon::parse($state)->format('H:i')),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListLibraries::route('/'),
            'create' => Pages\CreateLibrary::route('/create'),
            'edit' => Pages\EditLibrary::route('/{record}/edit'),
        ];
    }
}
