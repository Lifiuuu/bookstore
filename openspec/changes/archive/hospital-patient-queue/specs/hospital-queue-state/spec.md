## ADDED Requirements

### Requirement: Queue state is stored per polyclinic in Laravel Cache
The system SHALL persist each polyclinic's queue state as a JSON-encoded array in Laravel Cache under the key `hospital_queue:{poli_id}` with a 7200-second TTL.

#### Scenario: Default state returned for new or expired polyclinic
- **WHEN** `getState($poliId)` is called and no cache entry exists for `hospital_queue:{poli_id}`
- **THEN** the system SHALL return the default state: `{poli_id, poli_name, version: 0, next_number: 1, current: null, waiting: [], absent: []}`

#### Scenario: State persists between requests
- **WHEN** `saveState($poliId, $state)` is called after a mutation
- **THEN** the system SHALL increment `state['version']` by 1, store the updated state with a 7200s TTL, and the next `getState()` call for the same `poli_id` SHALL return the incremented state

### Requirement: Write operations use Cache lock to prevent race conditions
The system SHALL acquire a named Cache lock before executing any write mutation to prevent concurrent write conflicts.

#### Scenario: Lock acquired before state mutation
- **WHEN** `saveState($poliId, $state)` is called
- **THEN** the system SHALL use `Cache::lock("hospital_queue:write:{poli_id}", 5)` to wrap the `Cache::put()` call, releasing the lock after the write completes

### Requirement: Priority ordering governs next patient selection
The system SHALL select the next patient to call using a deterministic sort: primary key `priority` ascending (1=Emergency, 2=Lansia/Disabilitas, 3=Normal), secondary key `registered_at` ascending.

#### Scenario: Emergency patient called before normal patient regardless of registration order
- **WHEN** the waiting list contains a Normal patient registered before a Darurat patient and `adminCall()` is invoked
- **THEN** the system SHALL select and call the Darurat patient, not the Normal patient

#### Scenario: Two patients of equal priority ordered by registration time
- **WHEN** two Normal patients are in the waiting list
- **THEN** the one with the earlier `registered_at` timestamp SHALL be selected first

### Requirement: Polyclinic list is a fixed configuration in the controller
The system SHALL maintain a static array of available polyclinics in `HospitalQueueController` containing at minimum: Umum, Anak, Gigi, Kandungan, Penyakit Dalam, Mata — each with a kebab-case `id` and a display `name`.

#### Scenario: Polyclinic list is accessible to all controller methods
- **WHEN** any controller method calls `$this->polyclinics`
- **THEN** it SHALL return the complete configured polyclinic array without a database query
