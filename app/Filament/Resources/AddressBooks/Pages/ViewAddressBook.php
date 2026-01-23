<?php

namespace App\Filament\Resources\AddressBooks\Pages;

use App\Filament\Resources\AddressBooks\AddressBookResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewAddressBook extends ViewRecord
{
    protected static string $resource = AddressBookResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
