## MODIFIED Requirements

### Requirement: Sidebar navigation includes Queue System module
The sidebar navigation (rendered in `resources/views/layouts/sidebar.blade.php`) SHALL include a new collapsible menu item titled "Queue System (SSE)" with an MDI icon (`mdi-clock-fast`). The item SHALL expand to show three sub-links: "Daftar Antrian" → `/queue/guest`, "Admin Panel" → `/queue/admin`, "Papan Antrian" → `/queue/board`. The menu item SHALL be marked `active` (expanded) when the current route starts with `queue.`.

#### Scenario: User is on a queue route
- **WHEN** the user navigates to any `/queue/*` URL
- **THEN** the "Queue System (SSE)" sidebar item is expanded and the matching sub-link is highlighted as active

#### Scenario: User is on a non-queue route
- **WHEN** the user is on `/dashboard` or any other route
- **THEN** the "Queue System (SSE)" sidebar item is collapsed (not expanded)
