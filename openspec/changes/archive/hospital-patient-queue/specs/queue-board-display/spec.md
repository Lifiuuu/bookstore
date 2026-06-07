## ADDED Requirements

### Requirement: Board displays all active polyclinics simultaneously
The system SHALL display a fullscreen board showing the currently called patient for each active polyclinic in a CSS Grid layout. Each polyclinic SHALL have a distinct card showing its name, the current patient's number, and the current patient's name.

#### Scenario: Board loads all polyclinic cards
- **WHEN** a user navigates to `/hospital-queue/board` and activates the board
- **THEN** the system SHALL render one card per configured polyclinic arranged in a responsive grid (3 columns on desktop, 2 on tablet, 1 on mobile)

#### Scenario: Card shows "Menunggu panggilan" when no patient is called
- **WHEN** a polyclinic's `current` field is null in the queue state
- **THEN** the card for that polyclinic SHALL display "—" as the number and "Menunggu panggilan" as the patient name placeholder

### Requirement: Board activates via user gesture overlay
The system SHALL display a fullscreen overlay on initial page load requiring a user click before establishing SSE connections and enabling audio, satisfying browser autoplay policies.

#### Scenario: Overlay blocks content until clicked
- **WHEN** the board page first loads
- **THEN** a fullscreen overlay with the button "Aktifkan Papan Antrian" SHALL be displayed, hiding the board content beneath it

#### Scenario: Activation enables SSE and audio
- **WHEN** the user clicks the activation button
- **THEN** the system SHALL hide the overlay, open one `EventSource` connection per configured polyclinic pointing to `/sse/hospital-queue-stream?poli={poli_id}`, and mark the audio context as activated

### Requirement: Board animates on state change
The system SHALL apply a visual animation to a polyclinic card when its currently called patient changes.

#### Scenario: Card pulses on new patient call
- **WHEN** an SSE message arrives with a new `version` and a changed `current.number` for a polyclinic
- **THEN** the corresponding card SHALL update its displayed number and name and play a CSS pulse/glow animation for 3 seconds

### Requirement: Board announces new calls via Web Speech API
The system SHALL play an audio chime followed by a synthesized speech announcement each time a new patient is called in any polyclinic, after the board has been activated.

#### Scenario: Announcement plays on new call
- **WHEN** the board is activated and an SSE message arrives with a new `current.number` not equal to the last announced number for that polyclinic
- **THEN** the system SHALL play the chime audio (`/audio/dingdong.mp3`), then on the chime's `ended` event (or after 1s timeout), call `window.speechSynthesis.speak()` with the utterance "Perhatian. Pasien nomor [N], [Nama]. Harap menuju [Nama Poli]. Terima kasih." in language `id-ID` at rate `0.85`

#### Scenario: Graceful degradation when speech API unavailable
- **WHEN** `window.speechSynthesis` is undefined
- **THEN** the system SHALL play the chime-only audio and display a static warning badge "Pengumuman suara tidak didukung" on the board without throwing JavaScript errors
