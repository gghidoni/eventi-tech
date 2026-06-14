<?php

namespace App\Filament\Resources\Events\Schemas;

use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class EventInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informazioni Evento')
                    ->schema([
                        TextEntry::make('community.name')
                            ->label('Community'),
                        TextEntry::make('title'),
                        TextEntry::make('status')
                            ->badge(),
                        TextEntry::make('description')
                            ->columnSpanFull(),
                        TextEntry::make('type')
                            ->badge(),
                        TextEntry::make('start_date')
                            ->dateTime(),
                        TextEntry::make('end_date')
                            ->dateTime(),
                        TextEntry::make('website')
                            ->placeholder('-')
                            ->url(fn ($record) => $record->website)
                            ->openUrlInNewTab(),
                        TextEntry::make('tickets_url')
                            ->placeholder('-')
                            ->url(fn ($record) => $record->tickets_url)
                            ->openUrlInNewTab(),
                    ])
                    ->columns(2),

                Section::make('Indirizzo Evento')
                    ->schema([
                        TextEntry::make('address_book.region.name')
                            ->label('Regione')
                            ->placeholder('-'),
                        TextEntry::make('address_book.province.name')
                            ->label('Provincia')
                            ->placeholder('-'),
                        TextEntry::make('address_book.city.name')
                            ->label('Città')
                            ->placeholder('-'),
                        TextEntry::make('address_book.address_line')
                            ->label('Indirizzo')
                            ->placeholder('-')
                            ->columnSpanFull(),
                    ])
                    ->columns(3)
                    ->visible(fn ($record) => $record->address_book !== null)
                    ->collapsible(),

                Section::make('Immagini Poster')
                    ->schema([
                        ImageEntry::make('poster_img')
                            ->label('Poster Desktop')
                            ->height(250),
                    ])
                    ->collapsible()
                    ->collapsed(false),

                Section::make('Metadata')
                    ->schema([
                        TextEntry::make('created_at')
                            ->dateTime(),
                        TextEntry::make('updated_at')
                            ->dateTime(),
                    ])
                    ->columns(2)
                    ->collapsible()
                    ->collapsed(true),
            ]);
    }
}
