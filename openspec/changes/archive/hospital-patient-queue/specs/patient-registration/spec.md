## ADDED Requirements

### Requirement: Patient registration form displays available polyclinics
The system SHALL present a form allowing a patient to enter their name, select a target polyclinic from a predefined list, and select their priority category (Normal, Lansia, Disabilitas, Darurat).

#### Scenario: Form loads with polyclinic options
- **WHEN** a patient navigates to `/hospital-queue/register`
- **THEN** the system displays a name input field, a polyclinic dropdown containing at least Umum, Anak, Gigi, Kandungan, Penyakit Dalam, and Mata, and a priority selector with four options

#### Scenario: Name field is required
- **WHEN** a patient submits the form without entering a name
- **THEN** the system SHALL display a validation error "Nama pasien wajib diisi" and not assign a queue number

### Requirement: Queue number is assigned upon valid registration
The system SHALL assign a unique, auto-incrementing queue number scoped to the selected polyclinic and display it prominently upon successful submission.

#### Scenario: Successful patient registration
- **WHEN** a patient submits a valid name, polyclinic selection, and priority category
- **THEN** the system SHALL add the patient to the `waiting` list of the selected polyclinic's queue state, increment `next_number`, assign `registered_at` timestamp, and redirect back with a session flash containing the assigned number and polyclinic name

#### Scenario: Assigned number is displayed prominently
- **WHEN** a patient lands on the registration page after a successful submission
- **THEN** the system SHALL display a success card showing the assigned queue number in large text (≥72px) and the name of the selected polyclinic

### Requirement: Estimated wait time is shown after registration
The system SHALL display an indicative estimated wait time based on the patient's position in the waiting queue.

#### Scenario: Wait time estimate is displayed
- **WHEN** a patient receives their queue number
- **THEN** the system SHALL display an estimated wait time calculated as `(position_in_queue × 10)` minutes and label it clearly as an estimate
