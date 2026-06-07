## ADDED Requirements

### Requirement: Board view announces called number via Web Speech API
On the Board view (`/queue/board`), when the SSE stream delivers a new `current` entry with a different queue number than was previously shown, the system SHALL: (1) play the audio chime via `<audio id="chime" src="/audio/dingdong.mp3">`, then (2) use `window.speechSynthesis.speak()` to announce "Ting tong, nomor antrian [number]. [name]. Silakan masuk." in Indonesian (lang `id-ID`, or fallback to default voice).

#### Scenario: SSE delivers a new called number and board is activated
- **WHEN** the SSE stream sends a new `current` entry with a number different from the last announced number, and the user has already clicked the "Mulai" start button
- **THEN** the chime audio plays and then `speechSynthesis` speaks the announcement "Ting tong, nomor antrian [N]. [Name]. Silakan masuk."

#### Scenario: SSE delivers the same number as last announcement
- **WHEN** the SSE stream sends a state update but the `current.number` is unchanged from the last-announced number
- **THEN** no audio plays and no speech synthesis is triggered

#### Scenario: Web Speech API is unavailable in the browser
- **WHEN** `window.speechSynthesis` is undefined (unsupported browser)
- **THEN** the chime audio still plays (if available) and the board displays a warning badge "Speech API tidak didukung di browser ini" — the board continues to function for visual display

#### Scenario: Board has not been activated (start button not clicked)
- **WHEN** the SSE stream delivers a new called number but the start button has not been clicked
- **THEN** no audio or speech synthesis is triggered (SSE connection is not yet established)
