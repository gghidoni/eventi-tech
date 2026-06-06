<?php

namespace App\Filament\Resources\Tags\Schemas;

use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class TagInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name'),
                TextEntry::make('slug'),
                ImageEntry::make('icon')
                    ->label('Icona')
                    ->getStateUsing(fn ($record): string => 'https://cdn.simpleicons.org/'.rawurlencode((string) $record->icon).'/'.mb_ltrim((string) $record->label_color, '#')),
                TextEntry::make('icon'),
                TextEntry::make('badge_color'),
                TextEntry::make('label_color'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
