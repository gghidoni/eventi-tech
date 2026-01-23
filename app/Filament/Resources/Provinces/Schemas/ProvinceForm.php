<?php

namespace App\Filament\Resources\Provinces\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ProvinceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('code')
                    ->required()
                    ->maxLength(2),
                Select::make('region_id')
                    ->relationship('region', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
            ]);
    }
}
