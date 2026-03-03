<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class barang extends Model
{
    protected $table = 'barang'; // Nama tabel di database
    protected $fillable = ['nama', 'harga']; // Kolom yang dapat diisi

}
