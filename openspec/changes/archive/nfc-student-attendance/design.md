## Context

The system needs to register student attendance using physical NFC cards (KTM - Kartu Tanda Mahasiswa) directly from a mobile web browser. This bypasses the need for native Android/iOS development by utilizing the Web NFC API (`NDEFReader`). The backend will be a Laravel application providing both a UI for scanning and a REST API for recording attendance.

## Goals / Non-Goals

**Goals:**
- Provide a robust database schema to link unique NFC serial numbers to students.
- Implement an API endpoint that securely records attendance based on the hardware NFC ID.
- Create a mobile-first UI using Tailwind CSS that integrates with the Web NFC API.
- Ensure proper error handling on the client side for unsupported browsers and rejected permissions.

**Non-Goals:**
- Complex authentication for the scanning page itself (assuming it's accessed via an authenticated admin/lecturer session).
- Writing data to the NFC card (read-only mode is sufficient since we rely on the card's UID).
- Native app development.

## Decisions

- **Web NFC API**: Decided to use the experimental Web NFC API because it allows direct hardware access from the browser on supported devices (Android Chrome), significantly reducing development overhead compared to building a native app.
- **Hardware Serial Number vs NDEF Message**: We will read the hardware serial number of the NFC tag rather than requiring specialized NDEF records. This allows the system to work with any standard NFC card without prior formatting/encoding.
- **REST API Endpoint**: A separate POST endpoint `/api/attendance/record` will be created. The Blade view will use `fetch` to call this endpoint, avoiding full page reloads and providing a seamless "tap and go" experience.
- **Error Handling UI**: Use Tailwind to render a dynamic "status card" that changes colors (e.g., green for success, red for error, yellow for scanning) based on the current state.

## Risks / Trade-offs

- **Risk: Browser Compatibility** → Mitigation: Web NFC is currently only supported on Chrome for Android. We will include a try-catch block to detect support and display a clear warning if the user accesses the page from iOS or desktop.
- **Risk: Permission Denied** → Mitigation: Catch permission errors explicitly and provide a button to retry or instruct the user to allow NFC access.
- **Risk: Network Latency during Scan** → Mitigation: Show a clear "Processing..." state on the UI immediately after a successful scan to prevent users from tapping multiple times while waiting for the server response.
