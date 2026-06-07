## ADDED Requirements

### Requirement: SSE endpoint streams queue state to connected clients
The system SHALL expose `GET /sse/queue-stream` which opens a persistent HTTP connection using `response()->stream()` and sends Server-Sent Events. The response SHALL include headers: `Content-Type: text/event-stream`, `Cache-Control: no-cache`, `X-Accel-Buffering: no`. The stream SHALL emit a `data:` event containing the full queue state as JSON whenever the queue version increments. Between state changes the stream SHALL emit an SSE comment `:heartbeat` every poll cycle to keep the connection alive.

#### Scenario: Client connects to the stream and queue state changes
- **WHEN** a client opens an `EventSource` connection to `/sse/queue-stream` and the admin calls the next number
- **THEN** the client receives a `message` event within ≤2 seconds containing JSON with the updated `current`, `waiting`, and `skipped` arrays

#### Scenario: Client connects and no state changes occur
- **WHEN** a client is connected and no admin actions are taken for 10 seconds
- **THEN** the connection remains open (not dropped by proxy) and no `data:` events are emitted (only `:heartbeat` comments are sent)

#### Scenario: Client disconnects
- **WHEN** the client closes the browser tab or `EventSource` connection
- **THEN** the server's `while(true)` loop exits on the next iteration when `connection_aborted()` returns true
