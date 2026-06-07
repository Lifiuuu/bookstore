## ADDED Requirements

### Requirement: NFC Hardware Scanning Initialization
The frontend SHALL provide a user-initiated action (e.g., button click) to start scanning for NFC tags, as required by the Web NFC API.

#### Scenario: User starts scanning
- **WHEN** the user clicks the "Start NFC Scanner" button
- **THEN** the browser prompts for NFC permissions (if not previously granted) and initializes the `NDEFReader`

#### Scenario: Browser does not support Web NFC
- **WHEN** the user opens the page in an unsupported browser (e.g., Safari on iOS, desktop Chrome)
- **THEN** the UI displays an error message indicating that the browser is not supported and disables the scanning button

### Requirement: NFC Serial Number Extraction
The frontend SHALL extract the hardware serial number of any tapped NFC tag using the `NDEFReader` reading event.

#### Scenario: Tag is successfully read
- **WHEN** a valid NFC tag is tapped while the scanner is active
- **THEN** the frontend extracts the `serialNumber` and triggers the attendance recording API call

#### Scenario: Scanning fails or is interrupted
- **WHEN** an error occurs during reading (e.g., card removed too quickly)
- **THEN** the UI displays an error message and prompts the user to try tapping again
