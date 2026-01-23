<?php

namespace App\Filament\Resources\Events\Pages;

use App\Actions\ProcessPoster;
use App\Filament\Resources\Events\EventResource;
use Exception;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Storage;
use Log;

class CreateEvent extends CreateRecord
{
    protected static string $resource = EventResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Processa il poster se presente
        if (isset($data['poster_upload'])) {
            try {
                $processor = app(ProcessPoster::class);

                // Ottieni il file temporaneo
                $tempFile = Storage::disk('posters')->path('temp/'.$data['poster_upload']);

                if (file_exists($tempFile)) {
                    $result = $processor->execute($tempFile);

                    $data['poster'] = $result['desktop'];
                    $data['poster_mobile'] = $result['mobile'];
                    $data['poster_thumb'] = $result['thumb'];

                    // Rimuovi il file temporaneo
                    Storage::disk('posters')->delete('temp/'.$data['poster_upload']);
                }
            } catch (Exception $e) {
                Log::error('Image processing failed: '.$e->getMessage());
                Notification::make()
                    ->title('Errore nel processing dell\'immagine')
                    ->danger()
                    ->send();
            }

            unset($data['poster_upload']);
        }

        return $data;
    }
}
