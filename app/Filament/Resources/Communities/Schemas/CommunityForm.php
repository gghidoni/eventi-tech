<?php

namespace App\Filament\Resources\Communities\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ViewField;
use Filament\Schemas\Schema;

class CommunityForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('slug')
                    ->required()
                    ->maxLength(255),
                TextInput::make('status')
                    ->required()
                    ->default('pending')
                    ->maxLength(50),
                Textarea::make('description')
                    ->required()
                    ->maxLength(1000)
                    ->columnSpanFull(),
                TextInput::make('website')
                    ->url()
                    ->maxLength(255),

                // Preview dell'immagine attuale (solo in edit)
                ViewField::make('current_logo')
                    ->label('Logo Attuale')
                    ->view('filament.forms.components.current-image-preview')
                    ->visible(fn ($record) => $record && $record->logo)
                    ->viewData(fn ($record) => [
                        'url'   => $record->logo_img ?? null,
                        'label' => 'Logo Attuale',
                    ])
                    ->columnSpanFull(),

                FileUpload::make('logo_upload')
                    ->label('Carica Nuovo Logo')
                    ->helperText('Il sistema ottimizzerà automaticamente il logo (150px, WebP)')
                    ->image()
                    ->disk('logos')
                    ->directory('temp')
                    ->visibility('public')
                    ->imagePreviewHeight('150')
                    ->maxSize(1024)
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/svg+xml'])
                    ->downloadable()
                    ->openable()
                    ->avatar()
                    ->imageEditor()
                    ->imageEditorAspectRatios([
                        '1:1',
                    ])
                    ->columnSpanFull(),

                TextInput::make('linkedin')
                    ->maxLength(255),
                TextInput::make('instagram')
                    ->maxLength(255),
                TextInput::make('facebook')
                    ->maxLength(255),
                TextInput::make('phone')
                    ->tel()
                    ->maxLength(50),
            ]);
    }
}
