<?php

namespace App\Filament\Resources\Events\Schemas;

use App\Enums\EventStatus;
use App\Enums\EventType;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class EventForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('community_id')
                    ->relationship('community', 'name')
                    ->required(),
                TextInput::make('title')
                    ->required()
                    ->maxLength(100),
                Select::make('status')
                    ->options(EventStatus::class)
                    ->required(),
                Textarea::make('description')
                    ->required()
                    ->maxLength(1000)
                    ->columnSpanFull(),
                Select::make('type')
                    ->options(EventType::class)
                    ->required(),
                Select::make('address_book_id')
                    ->relationship('address_book', 'id')
                    ->searchable()
                    ->preload(),
                DateTimePicker::make('start_date')
                    ->required()
                    ->native(false),
                DateTimePicker::make('end_date')
                    ->required()
                    ->native(false),
                TextInput::make('website')
                    ->url()
                    ->maxLength(255),
                FileUpload::make('poster_upload')
                    ->label('Poster Evento')
                    ->helperText('Il sistema genererà automaticamente le versioni Desktop (1200px), Mobile (500px) e Thumbnail (150px)')
                    ->image()
                    ->disk('posters')
                    ->directory('temp')
                    ->visibility('public')
                    ->imagePreviewHeight('250')
                    ->maxSize(2048)
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                    ->downloadable()
                    ->openable()
                    ->imageEditor()
                    ->imageEditorAspectRatios([
                        '16:9',
                        '4:3',
                        '1:1',
                    ])
                    ->dehydrated(false)
                    ->columnSpanFull(),
                TextInput::make('tickets_url')
                    ->url()
                    ->maxLength(255),
                TextInput::make('cfp_url')
                    ->label('CFP URL')
                    ->url()
                    ->maxLength(255),
            ]);
    }
}
