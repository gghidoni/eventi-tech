<?php

namespace App\Filament\Resources\Events\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class EventInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('community.name')
                    ->label('Community'),
                TextEntry::make('title'),
                TextEntry::make('status')
                    ->badge(),
                TextEntry::make('description')
                    ->columnSpanFull(),
                TextEntry::make('type')
                    ->badge(),
                TextEntry::make('address_book.id')
                    ->label('Address book')
                    ->placeholder('-'),
                TextEntry::make('start_date')
                    ->dateTime(),
                TextEntry::make('end_date')
                    ->dateTime(),
                TextEntry::make('website')
                    ->placeholder('-'),
                TextEntry::make('poster')
                    ->placeholder('-'),
                TextEntry::make('poster_mobile')
                    ->placeholder('-'),
                TextEntry::make('poster_thumb')
                    ->placeholder('-'),
                TextEntry::make('tickets_url')
                    ->placeholder('-'),
                TextEntry::make('cfp_url')
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
