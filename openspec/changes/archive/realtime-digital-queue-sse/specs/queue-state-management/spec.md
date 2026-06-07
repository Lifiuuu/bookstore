## ADDED Requirements

### Requirement: Queue state is persisted in Laravel Cache
The system SHALL store the entire queue state under the key `queue:list` in Laravel Cache with a TTL of 3600 seconds. State mutations (add, call, skip, recall) SHALL use `Cache::lock('queue:write', 5)` to prevent race conditions. The state structure SHALL include: `version` (int), `next_number` (int), `current` (object|null), `waiting` (array), `skipped` (array).

#### Scenario: First guest registers (empty cache)
- **WHEN** no `queue:list` key exists in cache and a guest registers
- **THEN** the system initializes the state with `version: 1`, `next_number: 2`, `current: null`, `waiting: [{number: 1, name: "...", status: "waiting"}]`, `skipped: []` and stores it with a 3600s TTL

#### Scenario: Concurrent admin actions
- **WHEN** two admin requests arrive simultaneously for `call` and `skip`
- **THEN** one acquires the cache lock, completes its mutation, releases the lock, and the other then acquires it and operates on the updated state — no data is lost

#### Scenario: Cache TTL expires
- **WHEN** 3600 seconds pass without any queue activity
- **THEN** the next guest registration reinitializes the queue from scratch (number starts at 1 again) — this is the intended ephemeral behavior

### Requirement: Queue version counter enables SSE change detection
The system SHALL increment `queue:list.version` on every state mutation. The SSE stream endpoint SHALL track the last-emitted version and only push a `data:` event when the current version differs from the last-emitted version.

#### Scenario: Admin calls next person
- **WHEN** `POST /queue/admin/call` succeeds
- **THEN** `version` is incremented by 1 and the next SSE poll cycle detects the version change and emits the updated state

#### Scenario: No admin actions between SSE polls
- **WHEN** the SSE loop wakes up and `version` equals the last-emitted version
- **THEN** no `data:` event is emitted — only a `:heartbeat` SSE comment is flushed
