<?php

namespace App\Filament\Resources\Events\Pages;

use App\Actions\ProcessPoster;
use App\Filament\Resources\Events\EventResource;
use Exception;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Log;

class CreateEvent extends CreateRecord
{
    protected static string $resource = EventResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Processa il poster se presente
        if (isset($data['poster_upload']) && !empty($data['poster_upload'])) {
            try {
                $processor = app(ProcessPoster::class);

                // Ottieni il file caricato
                $uploadedFileName = is_array($data['poster_upload'])
                    ? $data['poster_upload'][0]
                    : $data['poster_upload'];

                // Percorso completo del file temporaneo
                $tempPath = Storage::disk('posters')->path($uploadedFileName);

                if (file_exists($tempPath)) {
                    $mimeType = mime_content_type($tempPath) ?: null;

                    // Crea un UploadedFile object compatibile con ProcessPoster
                    $file = new UploadedFile(
                        $tempPath,
                        basename($tempPath),
                        $mimeType,
                        null,
                        true,
                    );

                    $result = $processor->execute($file);

                    $data['poster'] = $result['desktop'];
                    $data['poster_mobile'] = $result['mobile'];
                    $data['poster_thumb'] = $result['thumb'];

                    // Cleanup temp file
                    Storage::disk('posters')->delete($uploadedFileName);
                } else {
                    Log::warning('Temp file not found: '.$tempPath);
                }
            } catch (Exception $e) {
                Log::error('Image processing failed: '.$e->getMessage());
                Log::error('Stack trace: '.$e->getTraceAsString());

                Notification::make()
                    ->title('Errore nel processing dell\'immagine')
                    ->body($e->getMessage())
                    ->danger()
                    ->send();
            }

            unset($data['poster_upload']);
        }

        return $data;
    }
}
