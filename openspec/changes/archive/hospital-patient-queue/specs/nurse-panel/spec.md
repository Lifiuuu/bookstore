## ADDED Requirements

### Requirement: Nurse panel displays queue state per polyclinic
The system SHALL render the nurse panel with a polyclinic selector and three sections: Currently Called patient, Waiting List (with priority badges), and Absent List.

#### Scenario: Nurse selects a polyclinic
- **WHEN** a nurse navigates to `/hospital-queue/nurse?poli={poli_id}`
- **THEN** the system SHALL display the current queue state for the selected polyclinic, including the currently called patient card, the waiting list ordered by priority then registration time, and the absent list

#### Scenario: Waiting list shows priority badges
- **WHEN** the waiting list is rendered
- **THEN** each patient entry SHALL display a colored priority badge: red for Darurat, orange for Lansia/Disabilitas, grey for Normal, alongside the patient's name and queue number

### Requirement: Nurse can call the next patient by priority order
The system SHALL allow a nurse to call the next patient. The system MUST select the patient with the highest priority (lowest priority integer) from the waiting list, breaking ties by earliest `registered_at`.

#### Scenario: Call next when waiting list is non-empty
- **WHEN** a nurse clicks "Panggil Berikutnya"
- **THEN** the system SHALL move the top-priority waiting patient to `current` with `status: called`, remove them from the `waiting` array, increment `version`, save state, and return `{success: true, state}` as JSON

#### Scenario: Call next when waiting list is empty
- **WHEN** a nurse clicks "Panggil Berikutnya" and the `waiting` array is empty
- **THEN** the system SHALL return HTTP 422 with `{error: "Antrian kosong"}` and NOT modify the queue state

### Requirement: Nurse can mark the current patient as absent
The system SHALL allow a nurse to mark the currently called patient as absent (tidak hadir).

#### Scenario: Mark current patient as absent
- **WHEN** a nurse clicks "Tandai Tidak Hadir" and a current patient exists
- **THEN** the system SHALL move `current` to the `absent` array with `status: absent`, set `current` to null, increment `version`, save state, and return `{success: true}` as JSON

#### Scenario: Mark absent when no current patient
- **WHEN** a nurse clicks "Tandai Tidak Hadir" and `current` is null
- **THEN** the system SHALL return HTTP 422 with `{error: "Tidak ada pasien yang sedang dipanggil"}`

### Requirement: Nurse can recall an absent patient
The system SHALL allow a nurse to move an absent patient back to the top of the waiting list.

#### Scenario: Recall an absent patient
- **WHEN** a nurse clicks "Recall" on a patient in the absent list
- **THEN** the system SHALL remove the patient from `absent`, prepend them to `waiting` with `status: waiting` and `priority` preserved, increment `version`, save state, and return `{success: true}` as JSON

### Requirement: Nurse can reset a polyclinic queue session
The system SHALL allow a nurse to reset the entire queue for a polyclinic, clearing all patients and resetting the number counter to 1.

#### Scenario: Reset polyclinic queue
- **WHEN** a nurse clicks "Reset Antrian" and confirms the action
- **THEN** the system SHALL delete the cache key `hospital_queue:{poli_id}` and return `{success: true}`, and subsequent `getState()` calls SHALL return the default empty state

### Requirement: Nurse panel updates in real-time via SSE
The system SHALL establish an SSE connection on the nurse panel page and update all three queue sections without a page reload when the queue state version changes.

#### Scenario: SSE update on call action
- **WHEN** any mutation (call, skip, recall, reset) occurs and the SSE client receives a new version
- **THEN** the system SHALL update the Currently Called card, Waiting List, and Absent List in the DOM to reflect the new state without a full page reload
