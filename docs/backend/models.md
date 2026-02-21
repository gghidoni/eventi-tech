# Backend: Models (Relazioni, Accessor, Helper URL)

## Event

Fonte: `app/Models/Event.php`

Punti utili per UI e query:

- Cast:
  - `type` -> `App\Enums\EventType`
  - `status` -> `App\Enums\EventStatus`
- Relazioni:
  - `community()`, `address_book()`, `tags()`, `bookmarks()`
- Accessor usati nelle view:
  - `poster_img`, `poster_mobile_img`, `poster_thumb_img` (con placeholder)
  - `formatted_start_date`, `formatted_datetime_start`, `formatted_datetime_end`
  - `public_url`, `edit_url`
  - `is_mine` (confronta `community.user_id` con `auth()->id()`)

## Community

Fonte: `app/Models/Community.php`

- Relazioni: `user()`, `events()`
- Accessor:
  - `public_url`, `edit_url`
  - `logo_img` (fallback su ui-avatars)

## User

Fonte: `app/Models/User.php`

- Relazioni: `communities()`, `bookmarks()`
- Accessor:
  - `has_active_community` (basato su `CommunityStatus::Active`)
  - `avatar_img` (fallback su ui-avatars)

