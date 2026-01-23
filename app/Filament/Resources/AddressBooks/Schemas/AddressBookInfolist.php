<?php

namespace App\Filament\Resources\AddressBooks\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class AddressBookInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('region.name')
                    ->label('Region'),
                TextEntry::make('province.name')
                    ->label('Province'),
                TextEntry::make('city.name')
                    ->label('City'),
                TextEntry::make('address_line')
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
