<?php

namespace App\Filament\Resources\Users\Pages;

use App\Actions\ProcessAvatar;
use App\Filament\Resources\Users\UserResource;
use Exception;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Storage;
use Log;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Processa l'avatar se presente
        if (isset($data['avatar_upload'])) {
            try {
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
