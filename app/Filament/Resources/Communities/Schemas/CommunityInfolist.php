<?php

namespace App\Filament\Resources\Communities\Schemas;

use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CommunityInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informazioni Community')
                    ->schema([
                        ImageEntry::make('logo_img')
                            ->label('Logo')
                            ->circular()
                            ->height(150)
                            ->defaultImageUrl(function ($record) {
                                if (!$record) {
                                    return null;
                                }
                                $name = urlencode($record->name);

                                return "https://ui-avatars.com/api/?name={$name}&background=random&color=fff&size=150";
                            })
                            ->columnSpanFull(),
                        TextEntry::make('user.name')
                            ->label('User'),
                        TextEntry::make('name'),
                        TextEntry::make('slug'),
                        TextEntry::make('status')
                            ->badge(),
                        TextEntry::make('description')
                            ->columnSpanFull(),
                        TextEntry::make('website')
                            ->placeholder('-')
                            ->url(fn ($record) => $record->website)
                            ->openUrlInNewTab(),
                        TextEntry::make('linkedin')
                            ->placeholder('-')
                            ->url(fn ($record) => $record->linkedin)
                            ->openUrlInNewTab(),
                        TextEntry::make('instagram')
                            ->placeholder('-')
                            ->url(fn ($record) => $record->instagram)
                            ->openUrlInNewTab(),
                        TextEntry::make('facebook')
                            ->placeholder('-')
                            ->url(fn ($record) => $record->facebook)
                            ->openUrlInNewTab(),
                        TextEntry::make('phone')
                            ->placeholder('-'),
                    ])
                    ->columns(2),

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
