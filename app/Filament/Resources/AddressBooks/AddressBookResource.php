<?php

namespace App\Filament\Resources\AddressBooks;

use App\Filament\Resources\AddressBooks\Pages\CreateAddressBook;
use App\Filament\Resources\AddressBooks\Pages\EditAddressBook;
use App\Filament\Resources\AddressBooks\Pages\ListAddressBooks;
use App\Filament\Resources\AddressBooks\Pages\ViewAddressBook;
use App\Filament\Resources\AddressBooks\Schemas\AddressBookForm;
use App\Filament\Resources\AddressBooks\Schemas\AddressBookInfolist;
use App\Filament\Resources\AddressBooks\Tables\AddressBooksTable;
use App\Models\AddressBook\AddressBook;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class AddressBookResource extends Resource
{
    protected static ?string $model = AddressBook::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBookOpen;

    protected static ?string $recordTitleAttribute = 'address_line';

    public static function form(Schema $schema): Schema
    {
        return AddressBookForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return AddressBookInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AddressBooksTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListAddressBooks::route('/'),
            'create' => CreateAddressBook::route('/create'),
            'view'   => ViewAddressBook::route('/{record}'),
            'edit'   => EditAddressBook::route('/{record}/edit'),
        ];
    }
}
