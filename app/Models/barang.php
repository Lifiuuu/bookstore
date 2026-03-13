<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class barang extends Model
{
    protected $table = 'barang'; // Nama tabel di database
    protected $primaryKey = 'id_barang';
    public $incrementing = true;
    protected $keyType = 'int';
    // Table tidak memiliki kolom created_at / updated_at
    public $timestamps = false;
    protected $fillable = ['nama', 'harga']; // Kolom yang dapat diisi

}
