## 1. Backend: Controller and Routes

- [x] 1.1 Create `StudentController` (if it doesn't exist)
- [x] 1.2 Implement `showRegisterForm()` to fetch students where `nfc_serial_number` is null
- [x] 1.3 Implement `assignNfc(Request $request)` to validate input and update the selected student's record
- [x] 1.4 Add `GET /student/register-nfc` route in `routes/web.php` pointing to `showRegisterForm`
- [x] 1.5 Add `POST /api/student/assign-nfc` route in `routes/api.php` pointing to `assignNfc`

## 2. Frontend: Blade View & Layout

- [x] 2.1 Create `resources/views/student/register-nfc.blade.php`
- [x] 2.2 Add "Register NFC Card" menu item to `resources/views/layouts/sidebar.blade.php` under the "Absensi NFC" section
- [x] 2.3 Build the UI with Tailwind CSS (dropdown for students, status card, "Tap to Scan" button, visual indicator input, hidden input for serial, Submit button)

## 3. Frontend: JavaScript Logic

- [x] 3.1 Implement browser compatibility check for `NDEFReader`
- [x] 3.2 Add click event on "Tap to Scan" button to initialize `NDEFReader.scan()` and update UI to "Scanning" state
- [x] 3.3 Add `reading` event listener to extract `serialNumber` from the NFC card
- [x] 3.4 Populate the visible status field and hidden input with the scanned `serialNumber`
- [x] 3.5 Enable the "Submit Registration" button and apply success visual cues once a card is scanned
- [x] 3.6 Implement `try-catch` blocks to gracefully handle scanning errors, permission denials, and display alerts to the user
- [x] 3.7 Handle form submission via AJAX `fetch` to `/api/student/assign-nfc` and display success/error feedback
