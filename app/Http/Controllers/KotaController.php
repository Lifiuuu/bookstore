<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class KotaController extends Controller
{
    /**
     * Show kota select demo page
     */
    public function select()
    {
        return view('tm4.kota_select');
    }
}
