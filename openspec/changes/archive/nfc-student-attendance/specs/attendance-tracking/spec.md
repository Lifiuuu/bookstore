## ADDED Requirements

### Requirement: Record Attendance API Endpoint
The backend SHALL expose an API endpoint (`POST /api/attendance/record`) that accepts a JSON payload containing an NFC `serialNumber` to record a student's attendance.

#### Scenario: Successful attendance recording
- **WHEN** a valid `serialNumber` matching an existing student is received
- **THEN** the system creates a new record in the `attendances` table with the current timestamp and returns a JSON response with the student's name and scan time

#### Scenario: Unregistered NFC card
- **WHEN** a `serialNumber` is received that does not match any student in the database
- **THEN** the system returns an error JSON response (e.g., 404 Not Found) with a message "Card not registered"

### Requirement: Attendance Database Schema
The system SHALL maintain a robust schema for students and their associated attendance logs.

#### Scenario: Student creation with NFC mapping
- **WHEN** a new student is added to the system
- **THEN** their `nfc_serial_number` is stored as a unique, nullable string in the `students` table

#### Scenario: Logging attendance
- **WHEN** a successful attendance is recorded
- **THEN** a new row is added to the `attendances` table linking the `student_id` with a `scanned_at` timestamp and a `status`
