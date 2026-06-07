## Why

This Laravel sandbox project lacks any real-time, server-push capability demonstration. Adding a Real-Time Digital Queue System using Server-Sent Events (SSE) fills that gap — providing a working reference implementation of SSE, Web Speech API, and cache-backed ephemeral state management, all within the existing project structure.

## What Changes

- **New module**: `Queue System (SSE)` added to the sidebar navigation with dropdown sub-links for three role-based views.
- **New controller**: `QueueController` to handle all queue logic (registration, admin actions, SSE stream).
- **Cache-backed state**: Queue entries are stored in Laravel Cache (no new migration needed) as a JSON array keyed by `queue:list`.
- **New routes** (public, no auth required for Guest and Board; admin routes remain unprotected for sandbox simplicity):
  - `GET /queue/guest` — guest registration form
  - `POST /queue/guest/submit` — submit name, assign queue number
  - `GET /queue/admin` — admin control panel
  - `POST /queue/admin/call` — call next person in queue
  - `POST /queue/admin/skip` — mark current as skipped/late
  - `POST /queue/admin/recall` — recall a skipped entry
  - `GET /queue/board` — public waiting-room display board
  - `GET /sse/queue-stream` — SSE endpoint using `response()->stream()`
- **New Blade views**: `queue/guest.blade.php`, `queue/admin.blade.php`, `queue/board.blade.php`
- **Client-side SSE**: `EventSource` on admin and board views to receive push updates and dynamically update the DOM.
- **Web Speech API**: On the board view only, a speech synthesis announcement plays whenever a new number is called, along with an `<audio>` chime element.
- **Sidebar update**: New collapsible "Queue System (SSE)" menu item added to `layouts/sidebar.blade.php`.

## Capabilities

### New Capabilities

- `queue-guest-registration`: Guest-facing form to enter a name and receive an assigned queue number; number displayed immediately on submission.
- `queue-admin-panel`: Admin dashboard displaying the current waiting list, a "Call Next" button, and a "Terlambat" (skipped) list with individual "Recall" buttons; DOM updated live via SSE.
- `queue-board-display`: Public waiting-room display board showing the currently-called number and name in large text; announces via Web Speech API and plays audio chime on each new call.
- `queue-sse-stream`: SSE endpoint that streams the current queue state as JSON events using `response()->stream()` with proper SSE headers; includes a heartbeat to keep connections alive.
- `queue-state-management`: Ephemeral queue state stored in Laravel Cache (`queue:list` key); supports adding, calling, skipping, and recalling entries without a database migration.

### Modified Capabilities

- `sidebar-navigation`: New collapsible "Queue System (SSE)" dropdown entry added to `layouts/sidebar.blade.php`.

## Impact

- **Files modified**: `routes/web.php`, `resources/views/layouts/sidebar.blade.php`
- **Files created**: `app/Http/Controllers/QueueController.php`, `resources/views/queue/guest.blade.php`, `resources/views/queue/admin.blade.php`, `resources/views/queue/board.blade.php`
- **Static asset needed**: `public/audio/dingdong.mp3` (placeholder; user must supply actual audio file)
- **Dependencies**: Laravel Cache (file/redis driver already configured in sandbox), no new Composer packages required
- **Browser requirements**: SSE (`EventSource`) supported in all modern browsers; Web Speech API requires Chrome/Edge for full support
- **No breaking changes** to existing routes, controllers, or views
