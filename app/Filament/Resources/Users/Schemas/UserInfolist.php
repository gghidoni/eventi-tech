<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UserInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informazioni Utente')
                    ->schema([
                        ImageEntry::make('avatar_img')
                            ->label('Avatar')
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
                        TextEntry::make('name'),
                        TextEntry::make('email')
                            ->label('Email address'),
                        TextEntry::make('email_verified_at')
                            ->dateTime()
                            ->placeholder('-'),
                        IconEntry::make('is_admin')
                            ->boolean(),
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
                    ])
                    ->columns(2),

                Section::make('Two Factor Authentication')
                    ->schema([
                        TextEntry::make('two_factor_secret')
                            ->placeholder('-')
                            ->columnSpanFull(),
                        TextEntry::make('two_factor_recovery_codes')
                            ->placeholder('-')
                            ->columnSpanFull(),
                        TextEntry::make('two_factor_confirmed_at')
                            ->dateTime()
                            ->placeholder('-'),
                    ])
                    ->collapsible()
                    ->collapsed(true),

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
