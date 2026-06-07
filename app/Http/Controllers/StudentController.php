<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function showRegisterForm()
    {
        $students = \App\Models\Student::whereNull('nfc_serial_number')
                                       ->orWhere('nfc_serial_number', '')
                                       ->get();
                                       
        return view('student.register-nfc', compact('students'));
    }

    public function assignNfc(Request $request)
    {
        $request->merge([
            'nfc_serial_number' => strtoupper($request->nfc_serial_number)
        ]);

        if ($request->is_new_student) {
            $request->validate([
                'nim' => 'required|string|unique:students,nim',
                'name' => 'required|string|max:255',
                'nfc_serial_number' => 'required|string|unique:students,nfc_serial_number',
            ]);

            $student = \App\Models\Student::create([
                'nim' => $request->nim,
                'name' => $request->name,
                'nfc_serial_number' => $request->nfc_serial_number,
            ]);
        } else {
            $request->validate([
                'student_id' => 'required|exists:students,id',
                'nfc_serial_number' => 'required|string|unique:students,nfc_serial_number',
            ]);

            $student = \App\Models\Student::findOrFail($request->student_id);
            $student->nfc_serial_number = $request->nfc_serial_number;
            $student->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'NFC card registered successfully to ' . $student->name,
            'student' => $student
        ]);
    }
}
