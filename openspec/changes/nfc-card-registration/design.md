## Context

In the newly implemented NFC-Based Student Attendance System, students must have their physical NFC card (KTM) registered in the database before they can scan for attendance. A secure and seamless administrative interface is required to map hardware serial numbers to specific student records.

## Goals / Non-Goals

**Goals:**
- Provide a responsive UI for admins to select unregistered students.
- Integrate Web NFC API to read hardware serial numbers.
- Securely update the student record with the scanned serial number.

**Non-Goals:**
- Implementing a complete CRUD interface for managing all student details.
- Supporting legacy browsers without Web NFC API (the admin must use a compatible mobile browser, such as Chrome on Android).

## Decisions

**Decision 1: Controller Separation**
- Use a dedicated `StudentController` for handling the `nfc_serial_number` assignment, rather than mixing it with `AttendanceController`. This adheres to RESTful patterns where updating a student belongs to a student-focused controller.

**Decision 2: UI State Flow**
- Implement visual cues to guide the admin:
  1. Default State: "Step 1: Select Student", Scan button active.
  2. Scanning State: "NFC Antenna Active", waiting for tap.
  3. Scanned State: Serial populated, Scan button disabled, "Submit" button enabled.
  4. Success State: Form reset or redirect back with a success flash message.

**Decision 3: Backend Validation**
- The backend will enforce unique constraints on `nfc_serial_number` and ensure the student `id` exists before committing changes.

## Risks / Trade-offs

- **Risk: Web NFC API compatibility**
  - **Trade-off:** Admins are required to use specific Android mobile devices with Chrome to perform registration.
  - **Mitigation:** Gracefully handle `NotAllowedError` and unsupported API checks in JS, providing clear alerts if the device is incapable.
