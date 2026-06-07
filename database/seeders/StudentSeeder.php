<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StudentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\Student::create([
            'nim' => '1001',
            'name' => 'John Doe',
            'nfc_serial_number' => '04:8E:7F:1A:E2:2C:80', // Dummy NFC ID
        ]);

        \App\Models\Student::create([
            'nim' => '1002',
            'name' => 'Jane Smith',
            'nfc_serial_number' => 'dummy-nfc-id-12345',
        ]);
    }
}
