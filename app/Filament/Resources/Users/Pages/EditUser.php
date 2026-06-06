<?php

namespace App\Filament\Resources\Users\Pages;

use App\Actions\ProcessAvatar;
use App\Filament\Concerns\HandlesSingleFileUpload;
use App\Filament\Resources\Users\UserResource;
use Exception;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Log;

class EditUser extends EditRecord
{
    use HandlesSingleFileUpload;

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
        if (isset($data['avatar_upload']) && !empty($data['avatar_upload'])) {
            try {
                // Elimina il vecchio avatar se esiste
                /** @var \App\Models\User $record */
                $record = $this->record;
                if ($record->avatar) {
                    Storage::disk('public')->delete($record->avatar);
                }

                $processor = app(ProcessAvatar::class);

                // Ottieni il file caricato
                $uploadedFileName = $this->extractSingleUploadPath($data['avatar_upload']);

                if ($uploadedFileName === null) {
                    unset($data['avatar_upload']);

                    return $data;
                }

                // Percorso completo del file temporaneo
                $tempPath = Storage::disk('public')->path($uploadedFileName);

                if (file_exists($tempPath)) {
                    $mimeType = mime_content_type($tempPath) ?: null;

                    // Crea un UploadedFile object compatibile con ProcessAvatar
                    $file = new UploadedFile(
                        $tempPath,
                        basename($tempPath),
                        $mimeType,
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
