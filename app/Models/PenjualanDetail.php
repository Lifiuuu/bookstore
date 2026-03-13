<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PenjualanDetail extends Model
{
    protected $table = 'penjualan_detail';
    protected $primaryKey = 'idpenjualan_detail';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;
    protected $fillable = ['id_penjualan', 'id_barang', 'jumlah', 'subtotal'];

    public function penjualan()
    {
        return $this->belongsTo(Penjualan::class, 'id_penjualan', 'id_penjualan');
    }

    public function barang()
    {
        return $this->belongsTo(\App\Models\barang::class, 'id_barang', 'id_barang');
    }
}
