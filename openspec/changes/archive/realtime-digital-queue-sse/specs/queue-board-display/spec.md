## ADDED Requirements

### Requirement: Board displays the currently-called queue number prominently
The system SHALL provide a full-screen display page at `GET /queue/board` that shows the currently-called queue number and name in large, high-contrast text, designed for projection on a waiting-room TV or monitor. The board SHALL update in real-time via SSE.

#### Scenario: A number is currently called
- **WHEN** the board view is active and there is a current called entry
- **THEN** the board prominently displays the queue number (e.g., "007") and the name (e.g., "Budi Santoso")

#### Scenario: No number is currently called
- **WHEN** the board view is active and no entry is currently called
- **THEN** the board displays a placeholder "— — —" in the number field and "Menunggu panggilan..." in the name field

#### Scenario: Board updates without page reload
- **WHEN** the admin calls the next number while the board is open in a browser
- **THEN** the board's number and name fields update automatically within 2 seconds via the SSE connection without any manual refresh

### Requirement: Board requires user activation before audio plays
The system SHALL display a full-screen overlay on first load with a "Mulai Papan Antrian" button. Audio and SSE connections SHALL only be initialized AFTER the user clicks this button, satisfying browser autoplay policies.

#### Scenario: User has not yet clicked the start button
- **WHEN** the board page loads
- **THEN** a full-screen overlay button is shown and no SSE connection or audio is active

#### Scenario: User clicks the start button
- **WHEN** the user clicks "Mulai Papan Antrian"
- **THEN** the overlay disappears, the SSE connection is established, and the board is ready to receive and announce calls
