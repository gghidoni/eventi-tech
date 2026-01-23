<?php

namespace App\Filament\Resources\Communities\Pages;

use App\Actions\ProcessLogo;
use App\Filament\Resources\Communities\CommunityResource;
use Exception;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Storage;
use Log;

class CreateCommunity extends CreateRecord
{
    protected static string $resource = CommunityResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Processa il logo se presente
        if (isset($data['logo_upload'])) {
            try {
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
