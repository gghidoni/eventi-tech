<?php

namespace App\Filament\Resources\Tags\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TagsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('slug')
                    ->searchable()
                    ->sortable(),
                ImageColumn::make('icon')
                    ->label('Icona')
                    ->getStateUsing(fn ($record): string => 'https://cdn.simpleicons.org/'.rawurlencode((string) $record->icon).'/'.ltrim((string) $record->label_color, '#'))
                    ->size(20),
                TextColumn::make('icon')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('badge_color')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => mb_strtoupper($state))
                    ->color('gray')
                    ->copyable(),
                TextColumn::make('label_color')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => mb_strtoupper($state))
                    ->color('gray')
                    ->copyable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
