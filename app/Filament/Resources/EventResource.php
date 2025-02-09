<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EventResource\Pages;
use App\Models\Event;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;


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
                                    ->live(onBlur: true)
                                    ->unique(ignoreRecord: true)
                                    ->label('Nama Event')
                                    ->afterStateUpdated(function (string $operation, $state, Set $set) {
                                        if ($operation !== 'create') {
                                            return;
                                        }

                                        $set('slug', Str::slug($state));
                                    }),
                                TextInput::make('slug')
                                    ->required()
                                    ->disabled()
                                    ->dehydrated()
                                    ->unique(Event::class, 'slug', ignoreRecord: true),
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
                                // DatePicker::make('registration_date')
                                //     ->required()
                                //     ->label('Waktu Pendaftaran')
                                //     ->native(false)
                                //     ->minDate(now()->toDateString())
                                //     ->afterStateUpdated(function (Set $set) {
                                //         $set('registration_end_date', null);
                                //     }),

                                // DatePicker::make('registration_end_date')
                                //     ->required()
                                //     ->label('Waktu Selesai Pendaftaran')
                                //     ->native(false)
                                //     ->dehydrated()
                                //     ->minDate(function (callable $get) {
                                //         $registration_date = $get('registration_date');
                                //         if ($registration_date) {
                                //             return $registration_date->addDay()->toDateString();
                                //         }

                                //         return now()->addDay()->toDateString();
                                //     })
                                //     ->rule('after:registration_date'),

                                DatePicker::make('start_date')
                                    ->required()
                                    ->label('Tanggal Mulai')
                                    ->native(false)
                                    ->minDate(function (callable $get) {
                                        $startDate = $get('registration_end_date');

                                        if ($startDate) {
                                            return Carbon::parse($startDate)->addDay()->toDateString();
                                        }

                                        return now()->addDay()->toDateString();
                                    })
                                    ->reactive()
                                    ->afterStateUpdated(function (callable $set) {
                                        $set('end_date', null);
                                    })
                                    ->rule('after:registration_end_date'),

                                DatePicker::make('end_date')
                                    ->required()
                                    ->label('Tanggal Selesai')
                                    ->native(false)
                                    ->minDate(function (callable $get) {
                                        $startDate = $get('start_date');

                                        if ($startDate) {
                                            return Carbon::parse($startDate)->addDay()->toDateString();
                                        }

                                        return now()->addDay()->toDateString();
                                    })
                                    ->rule('after:start_date'),
                                TimePicker::make('start_time')
                                    ->required()
                                    ->label('Waktu Mulai')
                                    ->rules([
                                        function (callable $get) {
                                            $startDate = $get('start_date');
                                            if ($startDate && Carbon::parse($startDate)->isToday()) {
                                                return 'after:' . now()->format('H:i');
                                            }

                                            return null;
                                        }
                                    ]),

                                TimePicker::make('end_time')
                                    ->required()
                                    ->label('Waktu Selesai')
                                    ->rules([
                                        function (callable $get) {
                                            $startDate = $get('start_date');
                                            $endDate = $get('end_date');
                                            $startTime = $get('start_time');

                                            $rules = [];
                                            if ($startDate && $endDate && ($startDate === $endDate)) {
                                                $rules[] = 'after:' . $startTime;
                                            }

                                            if ($endDate && Carbon::parse($endDate)->isToday()) {
                                                $rules[] = 'after:' . now()->format('H:i');
                                            }

                                            return $rules;
                                        }
                                    ]),
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
                                    ->relationship('eventType', 'name'),
                                RichEditor::make('event_overview')
                                    ->columnSpan('full')
                                    ->required()
                                    ->label('Detail Event'),
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
