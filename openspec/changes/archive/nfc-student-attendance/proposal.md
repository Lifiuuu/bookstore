## Why

To automate and streamline student attendance recording using modern mobile web technologies. The Web NFC API allows us to leverage existing Android devices to read NFC cards/KTMs directly from a web browser without a native app, creating a fast, efficient, and cost-effective attendance tracking solution.

## What Changes

- Add an "NFC Attendance" option to the global sidebar navigation linking to `/attendance/scan`.
- Create database models and migrations for `students` and `attendances` to track NFC card IDs and attendance logs.
- Implement an API endpoint (`/api/attendance/record`) to receive NFC scan payloads, validate student records, and log attendance.
- Build a mobile-responsive frontend scanner interface (`scan.blade.php`) using Tailwind CSS and the Web NFC API (`NDEFReader`).
- Implement robust frontend state management and error handling (success, not found, unsupported browser, permission denied).
- Provide database seeders with dummy student data for testing.

## Capabilities

### New Capabilities
- `nfc-scanning`: Web NFC frontend implementation for reading hardware serial numbers.
- `attendance-tracking`: Backend API and database schema for recording and validating student attendance.

### Modified Capabilities

## Impact

- **Database**: Adds two new tables (`students`, `attendances`).
- **Routing**: Adds a new web route (`/attendance/scan`) and a new API route (`/api/attendance/record`).
- **UI/Frontend**: Adds a new Tailwind-styled Blade view focused on mobile-responsiveness and introduces Web NFC API dependencies for supported browsers (primarily Chrome on Android).
