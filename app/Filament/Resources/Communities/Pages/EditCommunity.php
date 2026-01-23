<?php

namespace App\Filament\Resources\Communities\Pages;

use App\Actions\ProcessLogo;
use App\Filament\Resources\Communities\CommunityResource;
use Exception;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Storage;
use Log;

class EditCommunity extends EditRecord
{
    protected static string $resource = CommunityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Processa il logo se presente un nuovo upload
        if (isset($data['logo_upload'])) {
            try {
                // Elimina il vecchio logo se esiste
                /** @var \App\Models\Community $record */
                $record = $this->record;
                if ($record->logo) {
                    Storage::disk('logos')->delete($record->logo);
                }

                $processor = app(ProcessLogo::class);

                // Ottieni il file temporaneo
                $tempFile = Storage::disk('logos')->path('temp/'.$data['logo_upload']);

                if (file_exists($tempFile)) {
                    $data['logo'] = $processor->execute($tempFile);

                    // Rimuovi il file temporaneo
                    Storage::disk('logos')->delete('temp/'.$data['logo_upload']);
                }
            } catch (Exception $e) {
                Log::error('Logo processing failed: '.$e->getMessage());
                Notification::make()
                    ->title('Errore nel processing del logo')
                    ->danger()
                    ->send();
            }

            unset($data['logo_upload']);
        }

        return $data;
    }
}
