<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\TahunAjar;

class SetTahunAjar
{
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Cek apakah session tahun_ajar_id sudah ada?
        if (!$request->session()->has('tahun_ajar_id')) {
            
            // 2. Jika belum, ambil tahun ajar yang statusnya 'status_aktif' = true
            $activeTahun = TahunAjar::where('status_aktif', true)->first();

            if ($activeTahun) {
                // Set default ke session
                $request->session()->put('tahun_ajar_id', $activeTahun->id);
                $request->session()->put('tahun_ajar_nama', $activeTahun->tahun . ' - ' . $activeTahun->semester);
            }
        }

        return $next($request);
    }
}