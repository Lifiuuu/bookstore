<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Student;
use App\Models\Attendance;

class AttendanceController extends Controller
{
    public function showScanner()
    {
        return view('attendance.scan');
    }

    public function record(Request $request)
    {
        $request->validate([
            'serialNumber' => 'required|string',
        ]);

        $serialNumber = strtoupper($request->input('serialNumber'));

        $student = Student::where('nfc_serial_number', $serialNumber)->first();

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Card not registered.',
            ], 404);
        }

        // Record attendance
        $attendance = Attendance::create([
            'student_id' => $student->id,
            'scanned_at' => now(),
            'status' => 'present',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Attendance recorded successfully.',
            'student_name' => $student->name,
            'scanned_at' => $attendance->scanned_at->format('Y-m-d H:i:s'),
        ]);
    }
}
