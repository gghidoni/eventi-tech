# Backend: Upload immagini e storage

## Actions di processing

- Logo: `app/Actions/ProcessLogo.php` (resize, webp, disk `logos`)
- Poster evento: `app/Actions/ProcessPoster.php` (desktop/mobile/thumb, webp, disk `posters`)
- Avatar: `app/Actions/ProcessAvatar.php` (cover 200x200, webp, disk `public` in `avatars/`)

Le actions usano `Intervention\Image\Laravel\Facades\Image`.

## Filesystem disks

Definiti in `config/filesystems.php`:

- `logos` -> `storage/app/public/communities/logos`
- `posters` -> `storage/app/public/events/posters`
- `public` -> `storage/app/public` (usato per `avatars/*`)

## Pattern Livewire (view)

Per upload con anteprima e loading state:

- `resources/views/pages/dashboard/communities/⚡create.blade.php`
- `resources/views/pages/dashboard/events/⚡create.blade.php`
- `resources/views/pages/dashboard/⚡profile.blade.php`

