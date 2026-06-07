## Context

The bookstore sandbox is a Laravel 10 app (Purple Admin template, Bootstrap 4, MDI icons) used to experiment with various features. It currently has no push-based real-time capability. This module adds a self-contained demonstration of Server-Sent Events (SSE) with a full queue management workflow across three user-facing roles: guest, admin, and public board display.

The app uses the file-based Cache driver by default (configurable to Redis). There is no WebSocket server, and we want no additional infrastructure dependencies beyond what Laravel ships with.

## Goals / Non-Goals

**Goals:**
- Demonstrate SSE using Laravel's `response()->stream()` without Laravel Echo or Pusher
- Provide a functional real-time queue system with guest registration, admin control, and a public board
- Store all queue state ephemerally in Laravel Cache — no new database migrations
- Integrate cleanly into the existing Purple Admin sidebar without altering existing layout logic
- Implement the Web Speech API announcement on the board view with graceful audio activation

**Non-Goals:**
- Authentication/authorization enforcement (sandbox only — routes are public)
- Persistent queue history or reporting
- Multi-tenant or multi-room queue support
- WebSocket or Laravel Echo / Pusher integration
- Redis as a hard requirement (file cache is sufficient for localhost demo)

## Decisions

### D1: Laravel Cache over Database

**Decision**: Use `Cache::put('queue:list', [...], 3600)` to store the queue array.

**Rationale**: A migration would add boilerplate and permanent schema changes to a sandbox. Cache is ephemeral, fast, and requires zero extra setup — exactly right for a demo module.

**Alternative considered**: `Queue` entries in a `queue_entries` table with migrations. Rejected because it adds long-lived schema pollution and is overkill for an SSE demo.

---

### D2: `response()->stream()` over a dedicated SSE package

**Decision**: Use raw `response()->stream()` with a `while(true)` loop, `sleep(2)`, and manual `ob_flush()` / `flush()` calls.

**Rationale**: Keeps zero new Composer dependencies. The loop polls Cache every 2 seconds, detects changes via a version/hash counter stored alongside the queue list, and only emits a `data:` line when state actually changes (plus a `:heartbeat` comment every cycle to keep the connection alive through proxies).

**Alternative considered**: `spatie/laravel-sse` package. Rejected to avoid dependency bloat.

---

### D3: State change detection via a monotonic counter

**Decision**: Alongside `queue:list`, store `queue:version` (an integer incremented on every mutation). The SSE loop tracks the last-seen version and only sends a new `data:` event when the version changes.

**Rationale**: Prevents the client from receiving redundant updates every 2 seconds, reducing DOM thrashing.

---

### D4: Separate Board view with its own layout

**Decision**: The board view (`queue/board.blade.php`) uses a minimal full-screen layout (not the Purple Admin sidebar shell) to simulate a waiting-room TV display.

**Rationale**: The board is meant to run on a dedicated screen. Embedding the admin sidebar would be visually inappropriate and confusing. A separate layout costs almost nothing.

---

### D5: Web Speech API activation via explicit "Start" button

**Decision**: The board view renders a full-screen overlay button "Mulai Papan Antrian" on first load. Clicking it enables audio context and starts the SSE connection. This satisfies browser autoplay policies that block `speechSynthesis.speak()` and `HTMLAudioElement.play()` without a user gesture.

**Rationale**: Chrome and Firefox require a user gesture before audio APIs are usable. A start button is the cleanest UX pattern that satisfies this constraint without hacks.

---

### D6: Queue state shape

```json
{
  "version": 7,
  "next_number": 8,
  "current": { "number": 7, "name": "Budi", "status": "called" },
  "waiting": [
    { "number": 8, "name": "Siti", "status": "waiting" }
  ],
  "skipped": [
    { "number": 5, "name": "Andi", "status": "skipped" }
  ]
}
```

The SSE endpoint serializes this entire structure as a JSON string in each `data:` line.

## Risks / Trade-offs

| Risk | Mitigation |
|---|---|
| File-cache race condition on concurrent admin actions | Acceptable for sandbox; wrap mutations in a short Cache lock (`Cache::lock`) for safety |
| SSE connection drops on Nginx/Apache with output buffering enabled | Include `X-Accel-Buffering: no` header; document that `output_buffering = Off` should be set in PHP config for production use |
| `while(true)` loop ties up a PHP-FPM worker for the SSE connection duration | Acceptable for demo with few clients; document that a real production system would use Redis pub/sub or a dedicated SSE server |
| Web Speech API support varies by browser | Graceful fallback: if `window.speechSynthesis` is unavailable, only the audio chime plays; a warning toast is shown |
| Board view start button UX unfamiliar to end users | Label is clear: "Mulai Papan Antrian — Klik untuk mengaktifkan audio" |
