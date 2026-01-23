<?php

namespace App\Filament\Resources\Users\Pages;

use App\Actions\ProcessAvatar;
use App\Filament\Resources\Users\UserResource;
use Exception;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Storage;
use Log;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Processa l'avatar se presente un nuovo upload
        if (isset($data['avatar_upload'])) {
            try {
                // Elimina il vecchio avatar se esiste
                /** @var \App\Models\User $record */
                $record = $this->record;
                if ($record->avatar) {
                    Storage::disk('public')->delete($record->avatar);
                }

                $processor = app(ProcessAvatar::class);

                // Ottieni il file temporaneo
                $tempFile = Storage::disk('public')->path('temp/'.$data['avatar_upload']);

                if (file_exists($tempFile)) {
                    $data['avatar'] = $processor->execute($tempFile);

                    // Rimuovi il file temporaneo
                    Storage::disk('public')->delete('temp/'.$data['avatar_upload']);
                }
            } catch (Exception $e) {
                Log::error('Avatar processing failed: '.$e->getMessage());
                Notification::make()
                    ->title('Errore nel processing dell\'avatar')
                    ->danger()
                    ->send();
            }

            unset($data['avatar_upload']);
        }

        return $data;
    }
}
