<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RombelJadwalPelajaran;
use App\Models\RombelMataPelajaran;
use App\Models\Ruangan;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException; // <--- Wajib Import
use App\Http\Requests\StoreJadwalRequest;

class RombelJadwalPelajaranController extends Controller
{
    public function index()
    {
        $activeYear = session('tahun_ajar_id');

        // Filter jadwal yang HANYA milik tahun ajar aktif
        // Kita gunakan whereHas untuk menembus relasi rombelMapel
        $jadwal = RombelJadwalPelajaran::with(['rombelMapel.kelas', 'rombelMapel.mataPelajaran', 'rombelMapel.guru', 'ruangan'])
                    ->whereHas('rombelMapel', function($q) use ($activeYear) {
                        $q->where('id_tahun_ajar', $activeYear);
                    })
                    ->orderByRaw("FIELD(hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu')")
                    ->orderBy('jam_mulai')
                    ->paginate(20);

        return view('admin.rombel-jadwal.index', compact('jadwal'));
    }

    public function create()
    {
        $activeYear = session('tahun_ajar_id');

        // Ambil data plotting yang tersedia di tahun ini untuk dropdown
        // Format: X RPL 1 - Matematika (Pak Budi)
        $rombelMapel = RombelMataPelajaran::with(['kelas', 'mataPelajaran', 'guru'])
                        ->where('id_tahun_ajar', $activeYear)
                        ->get();

        $ruangan = Ruangan::all();

        return view('admin.rombel-jadwal.create', compact('rombelMapel', 'ruangan'));
    }

    public function store(StoreJadwalRequest $request)
    {
        // VALIDASI MANUAL DIHAPUS
        // Laravel otomatis menjalankan logic 'StoreJadwalRequest' sebelum masuk sini.
        // Jika bentrok, user otomatis dikembalikan ke form dengan pesan error.

        RombelJadwalPelajaran::create($request->all());

        return redirect()->route('admin.rombel-jadwal.index')
            ->with('success', 'Jadwal pelajaran berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $jadwal = RombelJadwalPelajaran::findOrFail($id);
        
        $activeYear = session('tahun_ajar_id');
        $rombelMapel = RombelMataPelajaran::with(['kelas', 'mataPelajaran', 'guru'])
                        ->where('id_tahun_ajar', $activeYear)
                        ->get();
        $ruangan = Ruangan::all();

        return view('admin.rombel-jadwal.edit', compact('jadwal', 'rombelMapel', 'ruangan'));
    }

    public function update(StoreJadwalRequest $request, $id)
    {
        // VALIDASI MANUAL DIHAPUS
        
        $jadwal = RombelJadwalPelajaran::findOrFail($id);
        $jadwal->update($request->all());

        return redirect()->route('admin.rombel-jadwal.index')
            ->with('success', 'Jadwal berhasil diperbarui.');
    }

    public function destroy($id)
    {
        try {
            $jadwal = RombelJadwalPelajaran::findOrFail($id);
            $jadwal->delete();

            return redirect()->route('admin.rombel-jadwal.index')->with('success', 'Jadwal berhasil dihapus.');

        } catch (QueryException $e) {
            // Menangani Error Foreign Key (Misal sudah ada absensi di jadwal ini)
            if ($e->getCode() == "23000") {
                return back()->with('error', 'Gagal Hapus! Jadwal ini sudah memiliki riwayat presensi siswa.');
            }
            
            return back()->with('error', 'Terjadi kesalahan database: ' . $e->getMessage());
        }
    }
}