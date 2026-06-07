## ADDED Requirements

### Requirement: Guest can register to join the queue
The system SHALL provide a form at `GET /queue/guest` where any visitor can enter their name. Upon submission via `POST /queue/guest/submit`, the system SHALL assign the next sequential queue number, persist the entry in Cache as `status: waiting`, and display the assigned number on the same page without a full page reload (via redirect-with-flash or inline rendering).

#### Scenario: Guest submits a valid name
- **WHEN** a guest navigates to `/queue/guest` and submits a non-empty name
- **THEN** the system assigns the next queue number, stores `{number, name, status: "waiting"}` in the cache queue list, increments the version counter, and redirects back displaying "Nomor antrian Anda: [N]"

#### Scenario: Guest submits an empty name
- **WHEN** a guest submits the registration form with an empty name field
- **THEN** the system SHALL return a validation error "Nama tidak boleh kosong" and re-render the form without assigning a queue number

#### Scenario: Queue is full or cache unavailable
- **WHEN** the cache driver is unavailable
- **THEN** the system SHALL show a friendly error message and not crash
