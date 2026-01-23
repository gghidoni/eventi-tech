<?php

namespace App\Filament\Resources\Events\Schemas;

use App\Enums\EventStatus;
use App\Enums\EventType;
use Filament\Forms\Components\DateTimePicker;
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
                    ->required(),
                Select::make('status')
                    ->options(EventStatus::class)
                    ->required(),
                Textarea::make('description')
                    ->required()
                    ->columnSpanFull(),
                Select::make('type')
                    ->options(EventType::class)
                    ->required(),
                Select::make('address_book_id')
                    ->relationship('address_book', 'id'),
                DateTimePicker::make('start_date')
                    ->required(),
                DateTimePicker::make('end_date')
                    ->required(),
                TextInput::make('website')
                    ->url(),
                TextInput::make('poster'),
                TextInput::make('poster_mobile'),
                TextInput::make('poster_thumb'),
                TextInput::make('tickets_url')
                    ->url(),
                TextInput::make('cfp_url')
                    ->url(),
            ]);
    }
}
