<?php

namespace App\Filament\Resources;

use App\Enums\EventStatus;
use App\Enums\EventType;
use App\Filament\Resources\EventResource\Pages;
use App\Filament\Resources\EventResource\RelationManagers;
use App\Models\Event;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class EventResource extends Resource
{
    protected static ?string $model = Event::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('title')->required()->maxLength(255),
                Textarea::make('description')->required()->maxLength(255),
                Select::make('status')->options(EventStatus::class),
                Select::make('type')->options(EventType::class),
                DateTimePicker::make('start_date')->required(),
                DateTimePicker::make('end_date')->required(),
                TextInput::make('website')->maxLength(255),
                TextInput::make('tickets_url')->maxLength(255),
                TextInput::make('cfp_url')->maxLength(255),
                Select::make('community_id')->relationship('community', 'name')->required(),
                Select::make('address_book_id')->relationship('address_book', 'id')->required()->createOptionForm([
                    Select::make('city_id')->relationship('city', 'name')->required()->preload()->searchable(),
                    Select::make('province_id')->relationship('province', 'name')->required()->preload()->searchable(),
                    Select::make('region_id')->relationship('region', 'name')->required(),
                    TextInput::make('address_line')->required()->maxLength(255),
                ]),
                FileUpload::make('poster')->disk('public')->directory('posters')->visibility('public')->image()->openable()->previewable(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->searchable(),
                TextColumn::make('status')->sortable(),
                TextColumn::make('start_date')->sortable(),
                TextColumn::make('end_date')->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\AddressBookRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListEvents::route('/'),
            'create' => Pages\CreateEvent::route('/create'),
            'edit'   => Pages\EditEvent::route('/{record}/edit'),
        ];
    }
}
