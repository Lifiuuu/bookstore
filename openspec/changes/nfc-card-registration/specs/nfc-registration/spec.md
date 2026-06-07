## ADDED Requirements

### Requirement: Display Unregistered Students
The system SHALL provide a dropdown list of students who do not have an NFC serial number assigned.

#### Scenario: Admin views registration page
- **WHEN** the admin navigates to the NFC registration page
- **THEN** the dropdown displays only students with a null or empty `nfc_serial_number`

### Requirement: Capture NFC Serial Number
The system SHALL use the Web NFC API to read the serial number of a tapped NFC card.

#### Scenario: Admin taps a card
- **WHEN** the admin clicks "Tap to Scan Card" and holds an NFC card near the device
- **THEN** the system captures the `serialNumber` and populates the hidden input and visible status field

#### Scenario: Web NFC unsupported
- **WHEN** the admin accesses the page on an unsupported browser
- **THEN** the system displays an error message indicating that an Android Chrome browser is required

### Requirement: Link NFC Card to Student
The system SHALL provide an API to link a scanned NFC serial number to a selected student record.

#### Scenario: Valid registration submission
- **WHEN** the admin submits a valid student ID and a scanned NFC serial number
- **THEN** the backend updates the student record and returns a success response

#### Scenario: Duplicate NFC card
- **WHEN** the admin submits an NFC serial number that is already linked to another student
- **THEN** the backend rejects the request with a validation error indicating the card is already in use
