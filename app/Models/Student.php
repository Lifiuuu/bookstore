<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    protected $fillable = [
        'nim',
        'name',
        'nfc_serial_number',
    ];

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }
}
