<?php

declare(strict_types=1);

namespace App\Filament\Concerns;

trait HandlesSingleFileUpload
{
    protected function extractSingleUploadPath(mixed $upload): ?string
    {
        if (is_string($upload) && $upload !== '') {
            return $upload;
        }

        if (!is_array($upload) || $upload === []) {
            return null;
        }

        $first = reset($upload);

        return is_string($first) && $first !== '' ? $first : null;
    }
}
