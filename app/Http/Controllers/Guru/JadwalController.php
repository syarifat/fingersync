<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\RombelJadwalPelajaran;
use Illuminate\Support\Facades\Auth;

class JadwalController extends Controller
{
    public function index()
    {
        $guru = Guru::where('user_id', Auth::id())->firstOrFail();

        // 1. Ambil SEMUA jadwal milik guru ini
        $semuaJadwal = RombelJadwalPelajaran::with(['rombelMataPelajaran.kelas', 'rombelMataPelajaran.mataPelajaran', 'ruangan'])
            ->whereHas('rombelMataPelajaran', function ($query) use ($guru) {
                $query->where('id_guru', $guru->id);
            })
            ->orderBy('jam_mulai', 'asc')
            ->get();

        // 2. Trik Cerdas: Urutkan hari secara logika (Senin -> Minggu), bukan abjad
        $urutanHari = ['Senin' => 1, 'Selasa' => 2, 'Rabu' => 3, 'Kamis' => 4, 'Jumat' => 5, 'Sabtu' => 6, 'Minggu' => 7];
        
        $jadwalPerHari = $semuaJadwal->sortBy(function($item) use ($urutanHari) {
            return $urutanHari[$item->hari] ?? 99;
        })->groupBy('hari');

        return view('guru.jadwal.index', compact('jadwalPerHari'));
    }
}