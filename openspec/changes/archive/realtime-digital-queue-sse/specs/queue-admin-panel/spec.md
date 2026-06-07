## ADDED Requirements

### Requirement: Admin can view the current queue state
The system SHALL provide a dashboard at `GET /queue/admin` that displays: (a) the currently-called entry (number + name), (b) the full ordered waiting list, and (c) the skipped/late list. The dashboard SHALL update in real-time via SSE without requiring a page reload.

#### Scenario: Admin loads the dashboard with entries in queue
- **WHEN** the admin navigates to `/queue/admin` and there are waiting entries
- **THEN** the page renders the waiting list, the current called entry (if any), and the skipped list with a "Recall" button for each skipped entry

#### Scenario: Admin loads the dashboard with an empty queue
- **WHEN** the admin navigates to `/queue/admin` and no entries exist
- **THEN** the page displays an empty state message "Antrian kosong" in each section

### Requirement: Admin can call the next person in line
The system SHALL expose `POST /queue/admin/call` which takes the first entry with `status: waiting`, changes its status to `called`, stores it as `current`, and increments the queue version counter.

#### Scenario: Admin calls next with waiting entries
- **WHEN** the admin clicks "Panggil Berikutnya" and there is at least one waiting entry
- **THEN** the system moves the first waiting entry to `current` with `status: called`, increments version, and SSE clients receive the updated state within 2 seconds

#### Scenario: Admin calls next with no waiting entries
- **WHEN** the admin clicks "Panggil Berikutnya" and the waiting list is empty
- **THEN** the system returns a JSON response `{success: false, message: "Tidak ada antrian menunggu"}` with HTTP 422

### Requirement: Admin can skip the currently called person
The system SHALL expose `POST /queue/admin/skip` which moves the current `called` entry to the skipped list with `status: skipped` and increments the version counter.

#### Scenario: Admin skips the current call
- **WHEN** the admin clicks "Tandai Terlambat" on the current entry
- **THEN** the system moves the current entry to the skipped array with `status: skipped`, clears `current`, increments version, and SSE clients receive the updated state

#### Scenario: Admin tries to skip with no current entry
- **WHEN** the admin triggers skip but no entry is currently called
- **THEN** the system returns `{success: false, message: "Tidak ada antrian aktif"}` with HTTP 422

### Requirement: Admin can recall a skipped person
The system SHALL expose `POST /queue/admin/recall` (with a `number` parameter) which moves the skipped entry back to the front of the waiting list with `status: waiting` and increments the version counter.

#### Scenario: Admin recalls a skipped entry
- **WHEN** the admin clicks "Recall" on a skipped entry
- **THEN** the system moves that entry from skipped to the front of the waiting list with `status: waiting`, increments version, and SSE clients receive the updated state

#### Scenario: Admin tries to recall a number not in the skipped list
- **WHEN** the admin submits a recall request with a number not in the skipped list
- **THEN** the system returns `{success: false, message: "Nomor tidak ditemukan di daftar terlambat"}` with HTTP 404
