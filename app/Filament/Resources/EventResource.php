<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EventResource\Pages;
use App\Filament\Resources\EventResource\RelationManagers;
use App\Models\Event;
use App\Models\EventCategory;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class EventResource extends Resource
{
    protected static ?string $model = Event::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationGroup = 'Event';

    protected static ?string $navigationLabel = 'Event';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Group::make()
                    ->schema([
                        Section::make('Event Details')
                            ->schema([
                                TextInput::make('title')
                                    ->required()
                                    ->maxLength(255)
                                    ->label('Nama Event'),
                                TextInput::make('participants_count')
                                    ->required()
                                    ->numeric()
                                    ->minValue(10)
                                    ->label('Jumlah Peserta'),
                                MarkdownEditor::make('description')
                                    ->fileAttachmentsDisk('public')
                                    ->fileAttachmentsDirectory('attachments')
                                    ->fileAttachmentsVisibility('private')
                                    ->columnSpan('full')
                                    ->required()
                                    ->label('Deskripsi'),
                            ])->columns(2),

                        Section::make('Jadwal Event')
                            ->schema([
                                DatePicker::make('start_date')
                                    ->required()
                                    ->label('Tanggal Mulai'),
                                DatePicker::make('end_date')
                                    ->required()
                                    ->label('Tanggal Selesai'),
                            ])->columns(2),
                    ]),
                Group::make()
                    ->schema([
                        Section::make('Jenis Event & Kategori Event')
                            ->schema([
                                Select::make('event_mode')
                                    ->required()
                                    ->label('Jenis Event')
                                    ->multiple()
                                    ->searchable()
                                    ->options([
                                        'offline' => 'Offline',
                                        'online' => 'Online',
                                    ]),
                                Select::make('event_category_id')
                                    ->required()
                                    ->label('Kategori Event')
                                    ->searchable()
                                    ->preload()
                                    ->relationship('eventType', 'name')
                            ])->columns(2),

                        Section::make('Image')
                            ->schema([
                                FileUpload::make('image')
                                    ->acceptedFileTypes(['image/png', 'image/jpeg', 'image/jpg'])
                                    ->required()
                                    ->directory('image-events')
                                    ->preserveFilenames()
                                    ->image()
                                    ->imageEditor()
                            ])->collapsible(),
                        Section::make('Pengaturan')
                            ->schema([
                                TextInput::make('location')
                                    ->label('Lokasi'),
                                Select::make('status')
                                    ->required()
                                    ->label('Status')
                                    ->searchable()
                                    ->options([
                                        'ongoing' => 'Sedang Berlangsung',
                                        'upcoming' => 'Akan Datang',
                                        'completed' => 'Selesai',
                                    ])
                            ])->columns(2),
                    ])

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Nama Event')
                    ->searchable(),
                TextColumn::make('event_mode')
                    ->label('Jenis Event'),
                TextColumn::make('eventType.name')
                    ->searchable()
                    ->label('Kategori Event'),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn($state) => match ($state) {
                        'ongoing' => 'warning',
                        'upcoming' => 'primary',
                        'completed' => 'success',
                    }),
                TextColumn::make('start_date')
                    ->label('Tanggal Mulai')
                    ->formatStateUsing(fn($state) => Carbon::parse($state)->format('d F Y')),
                TextColumn::make('end_date')
                    ->label('Tanggal Selesai')
                    ->formatStateUsing(fn($state) => Carbon::parse($state)->format('d F Y')),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
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
            'index' => Pages\ListEvents::route('/'),
            'create' => Pages\CreateEvent::route('/create'),
            'edit' => Pages\EditEvent::route('/{record}/edit'),
        ];
    }
}
