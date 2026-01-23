<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required()
                    ->maxLength(255),
                DateTimePicker::make('email_verified_at')
                    ->native(false),
                TextInput::make('password')
                    ->password()
                    ->required(fn ($context) => $context === 'create')
                    ->dehydrated(fn ($state) => filled($state))
                    ->maxLength(255),
                Toggle::make('is_admin')
                    ->required(),
                FileUpload::make('avatar_upload')
                    ->label('Avatar')
                    ->helperText('Il sistema ridimensionerà automaticamente l\'avatar (200x200px, WebP)')
                    ->image()
                    ->disk('public')
                    ->directory('temp')
                    ->visibility('public')
                    ->imagePreviewHeight('150')
                    ->maxSize(1024)
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                    ->downloadable()
                    ->openable()
                    ->avatar()
                    ->imageEditor()
                    ->imageEditorAspectRatios([
                        '1:1',
                    ])
                    ->dehydrated(false)
                    ->columnSpanFull(),
                TextInput::make('website')
                    ->url()
                    ->maxLength(255),
                TextInput::make('linkedin')
                    ->maxLength(255),
                TextInput::make('instagram')
                    ->maxLength(255),
                TextInput::make('facebook')
                    ->maxLength(255),
                Textarea::make('two_factor_secret')
                    ->columnSpanFull(),
                Textarea::make('two_factor_recovery_codes')
                    ->columnSpanFull(),
                DateTimePicker::make('two_factor_confirmed_at')
                    ->native(false),
            ]);
    }
}
