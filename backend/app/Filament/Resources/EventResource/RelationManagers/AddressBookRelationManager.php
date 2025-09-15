<?php

declare(strict_types=1);

namespace App\Filament\Resources\EventResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class AddressBookRelationManager extends RelationManager
{
    protected static string $relationship = 'address_book';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('address_line')
                    ->required()
                    ->maxLength(255),
                Select::make('city_id')->relationship('city', 'name')->required()->preload()->searchable(),
                Select::make('province_id')->relationship('province', 'name')->required()->preload()->searchable(),
                Select::make('region_id')->relationship('region', 'name')->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('address_line')
            ->columns([
                Tables\Columns\TextColumn::make('address_line'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
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
}
