<?php

namespace App\Filament\Resources\Communities\Pages;

use App\Actions\ProcessLogo;
use App\Filament\Concerns\HandlesSingleFileUpload;
use App\Filament\Resources\Communities\CommunityResource;
use Exception;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Log;

class EditCommunity extends EditRecord
{
    use HandlesSingleFileUpload;

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
        if (isset($data['logo_upload']) && !empty($data['logo_upload'])) {
            try {
                // Elimina il vecchio logo se esiste
                /** @var \App\Models\Community $record */
                $record = $this->record;
                if ($record->logo) {
                    Storage::disk('logos')->delete($record->logo);
                }

                $processor = app(ProcessLogo::class);

                // Ottieni il file caricato
                $uploadedFileName = $this->extractSingleUploadPath($data['logo_upload']);

                if ($uploadedFileName === null) {
                    unset($data['logo_upload']);

                    return $data;
                }

                // Percorso completo del file temporaneo
                $tempPath = Storage::disk('logos')->path($uploadedFileName);

                if (file_exists($tempPath)) {
                    $mimeType = mime_content_type($tempPath) ?: null;

                    // Crea un UploadedFile object compatibile con ProcessLogo
                    $file = new UploadedFile(
                        $tempPath,
                        basename($tempPath),
                        $mimeType,
                        null,
                        true,
                    );

                    $data['logo'] = $processor->execute($file);

                    // Cleanup temp file
                    Storage::disk('logos')->delete($uploadedFileName);
                } else {
                    Log::warning('Temp file not found: '.$tempPath);
                }
            } catch (Exception $e) {
                Log::error('Logo processing failed: '.$e->getMessage());
                Log::error('Stack trace: '.$e->getTraceAsString());

                Notification::make()
                    ->title('Errore nel processing del logo')
                    ->body($e->getMessage())
                    ->danger()
                    ->send();
            }

            unset($data['logo_upload']);
        }

        return $data;
    }
}
