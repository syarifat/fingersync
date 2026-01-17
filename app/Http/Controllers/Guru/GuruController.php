<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class GuruController extends Controller
{
    public function index()
    {
        // Mengambil data profil guru yang sedang login melalui relasi
        $guru = auth()->user()->guru; 
        return view('guru.dashboard', compact('guru'));
    }
}
