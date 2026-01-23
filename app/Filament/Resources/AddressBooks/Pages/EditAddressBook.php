<?php

namespace App\Filament\Resources\AddressBooks\Pages;

use App\Filament\Resources\AddressBooks\AddressBookResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditAddressBook extends EditRecord
{
    protected static string $resource = AddressBookResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
