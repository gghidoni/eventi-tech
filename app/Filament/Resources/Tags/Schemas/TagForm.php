<?php

namespace App\Filament\Resources\Tags\Schemas;

use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class TagForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (?string $state, Set $set, ?string $operation): void {
                        // In creazione, manteniamo lo slug allineato al nome.
                        if ($operation === 'create' && filled($state)) {
                            $set('slug', Str::slug($state));
                        }
                    })
                    ->required()
                    ->maxLength(255),
                TextInput::make('slug')
                    ->required()
                    ->maxLength(255),
                TextInput::make('icon')
                    ->label('Icona (Simple Icons slug)')
                    ->placeholder('es: laravel, php, docker')
                    ->required()
                    ->maxLength(255),
                ColorPicker::make('badge_color')
                    ->label('Colore badge')
                    ->required()
                    ->live()
                    ->afterStateUpdated(function (?string $state, Set $set): void {
                        // Il colore label viene calcolato automaticamente dal contrasto.
                        if (filled($state)) {
                            $set('label_color', self::pickLabelColorForBadge($state));
                        }
                    }),
                TextInput::make('label_color')
                    ->label('Colore label')
                    ->disabled()
                    ->dehydrated()
                    ->formatStateUsing(function (?string $state, Get $get): string {
                        $badgeColor = $get('badge_color');

                        return $state ?: self::pickLabelColorForBadge(is_string($badgeColor) ? $badgeColor : '#FFFFFF');
                    })
                    ->maxLength(255),
            ]);
    }

    /**
     * Restituisce un colore testo leggibile su un badge con questo sfondo.
     */
    private static function pickLabelColorForBadge(string $badgeColor): string
    {
        $hex = mb_ltrim($badgeColor, '#');

        if (mb_strlen($hex) !== 6) {
            return '#FFFFFF';
        }

        $red = hexdec(mb_substr($hex, 0, 2));
        $green = hexdec(mb_substr($hex, 2, 2));
        $blue = hexdec(mb_substr($hex, 4, 2));

        // Luminanza percepita (YIQ): soglia semplice per scegliere testo chiaro/scuro.
        $yiq = (($red * 299) + ($green * 587) + ($blue * 114)) / 1000;

        return $yiq >= 160 ? '#111827' : '#FFFFFF';
    }
}
