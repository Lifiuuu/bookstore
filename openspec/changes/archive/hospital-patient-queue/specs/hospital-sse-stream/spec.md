## ADDED Requirements

### Requirement: SSE endpoint streams queue state per polyclinic
The system SHALL expose a streaming endpoint at `GET /sse/hospital-queue-stream` that accepts a `?poli={poli_id}` query parameter and emits Server-Sent Events containing the full queue state JSON for the specified polyclinic.

#### Scenario: SSE connection established with valid poli parameter
- **WHEN** a client connects to `/sse/hospital-queue-stream?poli=umum`
- **THEN** the system SHALL respond with headers `Content-Type: text/event-stream`, `Cache-Control: no-cache`, `X-Accel-Buffering: no`, and begin streaming events

#### Scenario: State event emitted only on version change
- **WHEN** the SSE loop detects that the current `version` in cache differs from `$lastVersion`
- **THEN** the system SHALL emit `data: {json_encoded_state}\n\n` and update `$lastVersion` to the new version

#### Scenario: Heartbeat keeps connection alive
- **WHEN** no version change has occurred in the current poll cycle
- **THEN** the system SHALL emit `:heartbeat\n\n` to prevent proxy timeout disconnects

#### Scenario: Connection cleanup on client disconnect
- **WHEN** `connection_aborted()` returns true inside the stream loop
- **THEN** the system SHALL break out of the while loop and allow the response to terminate cleanly

#### Scenario: Invalid or missing poli parameter
- **WHEN** a client connects with a missing or unrecognized `poli` parameter
- **THEN** the system SHALL use `umum` as the default polyclinic and continue streaming normally

### Requirement: SSE stream is long-running without PHP timeout
The system SHALL configure the SSE closure to disable PHP execution time limits and ignore user connection abort to support long-lived connections.

#### Scenario: PHP time limit disabled
- **WHEN** the SSE stream closure begins execution
- **THEN** `set_time_limit(0)` and `ignore_user_abort(true)` SHALL be called before entering the while loop
