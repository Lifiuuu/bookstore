## Why

Currently, the NFC Attendance System allows scanning cards, but there is no dedicated interface to initially link a physical NFC card's hardware serial number to a specific student in the database. An admin-facing "NFC Card Registration Page" is needed to streamline this onboarding process, replacing manual database entries with an intuitive, scan-to-assign web interface.

## What Changes

- Add a new dedicated frontend view (`register-nfc.blade.php`) tailored for mobile browsers to facilitate card registration.
- Implement Web NFC API logic to read the `serialNumber` and bind it to a form.
- Add a new controller method and route (`GET /student/register-nfc`) to provide a list of students who currently do not have an NFC card assigned.
- Add a new API endpoint (`POST /api/student/assign-nfc`) to securely save the scanned NFC serial number to the selected student's record.

## Capabilities

### New Capabilities
- `nfc-registration`: Provides the admin interface and backend logic to map a physical NFC card serial number to a student's database record.

### Modified Capabilities
- (None)

## Impact

- `routes/web.php` and `routes/api.php` will get new routes for the registration flow.
- The `AttendanceController` or a new `StudentController` will handle the registration logic.
- A new Blade view `register-nfc.blade.php` will be introduced.
- Existing database schema (`students` table) will remain unchanged, but the `nfc_serial_number` field will be actively updated via the new API.
