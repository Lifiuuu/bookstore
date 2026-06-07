## 1. Database & Models

- [x] 1.1 Create migration for `students` table (`id`, `nim`, `name`, `nfc_serial_number`)
- [x] 1.2 Create `Student` Eloquent model with fillable properties and relationships
- [x] 1.3 Create migration for `attendances` table (`id`, `student_id`, `scanned_at`, `status`)
- [x] 1.4 Create `Attendance` Eloquent model with relationships to `Student`
- [x] 1.5 Create `StudentSeeder` to generate dummy data for testing (including a dummy `nfc_serial_number`)

## 2. Backend API & Routes

- [x] 2.1 Create `AttendanceController`
- [x] 2.2 Add `GET /attendance/scan` route in `web.php` linking to `AttendanceController@showScanner`
- [x] 2.3 Add `POST /api/attendance/record` API route in `api.php` linking to `AttendanceController@record`
- [x] 2.4 Implement `showScanner` method to return the `attendance.scan` Blade view
- [x] 2.5 Implement `record` method logic to find student by NFC, log attendance, and return JSON response

## 3. Sidebar Integration

- [x] 3.1 Locate the global sidebar navigation file in the existing layout
- [x] 3.2 Add an "NFC Attendance" menu item that points to the `attendance.scan` route

## 4. Frontend Scanner (Blade & JS)

- [x] 4.1 Create `resources/views/attendance/scan.blade.php`
- [x] 4.2 Build mobile-responsive UI with Tailwind CSS (Start button, status text, result card)
- [x] 4.3 Implement JavaScript logic to initialize `NDEFReader` on button click
- [x] 4.4 Add event listener for `reading` event to extract `serialNumber`
- [x] 4.5 Implement `fetch` API call to send `serialNumber` to `/api/attendance/record`
- [x] 4.6 Implement UI state updates (Idle, Scanning, Processing, Success, Error)
- [x] 4.7 Add try-catch block to handle unsupported browsers and permission errors gracefully
