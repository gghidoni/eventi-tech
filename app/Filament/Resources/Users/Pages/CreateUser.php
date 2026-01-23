<?php

namespace App\Filament\Resources\Users\Pages;

use App\Actions\ProcessAvatar;
use App\Filament\Resources\Users\UserResource;
use Exception;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Log;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Processa l'avatar se presente
        if (isset($data['avatar_upload']) && !empty($data['avatar_upload'])) {
            try {
                $processor = app(ProcessAvatar::class);

                // Ottieni il file caricato
                $uploadedFileName = is_array($data['avatar_upload'])
                    ? $data['avatar_upload'][0]
                    : $data['avatar_upload'];

                // Percorso completo del file temporaneo
                $tempPath = Storage::disk('public')->path($uploadedFileName);

                if (file_exists($tempPath)) {
                    // Crea un UploadedFile object compatibile con ProcessAvatar
                    $file = new UploadedFile(
                        $tempPath,
                        basename($tempPath),
                        mime_content_type($tempPath),
                        null,
                        true,
                    );

                    $data['avatar'] = $processor->execute($file);

                    // Cleanup temp file
                    Storage::disk('public')->delete($uploadedFileName);
                } else {
                    Log::warning('Temp file not found: '.$tempPath);
                }
            } catch (Exception $e) {
                Log::error('Avatar processing failed: '.$e->getMessage());
                Log::error('Stack trace: '.$e->getTraceAsString());

                Notification::make()
                    ->title('Errore nel processing dell\'avatar')
                    ->body($e->getMessage())
                    ->danger()
                    ->send();
            }

            unset($data['avatar_upload']);
        }

        return $data;
    }
}
