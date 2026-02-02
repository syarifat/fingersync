<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Presensi;
use App\Models\TahunAjar; // Pastikan model ini ada
use App\Models\RombelJadwalPelajaran; // Pastikan model ini ada
use Illuminate\Http\Request;
use Carbon\Carbon;

class PresensiController extends Controller
{
    /**
     * Menampilkan daftar absensi
     */
    public function index()
    {
        // Mengambil data presensi dengan relasinya
        // Relasi: siswa, jadwal, device, tahunAjar harus ada di Model Presensi
        $dataPresensi = Presensi::with(['siswa', 'jadwal', 'device', 'tahunAjar'])
                                ->latest()
                                ->paginate(10);

        return view('admin.presensi.index', compact('dataPresensi'));
    }

    /**
     * Menyimpan data (Manual Input oleh Admin atau Test API)
     */
    public function store(Request $request)
    {
        // 1. Validasi Input Dasar
        $request->validate([
            'id_siswa'  => 'required|exists:siswa,id',
            'id_device' => 'required|exists:device,id',
            'status'    => 'required|in:Hadir,Izin,Sakit,Terlambat,Alpa',
        ]);

        // 2. Tentukan Waktu Sekarang
        $tanggalSekarang = Carbon::now()->format('Y-m-d');
        $jamSekarang     = Carbon::now()->format('H:i:s');
        $hariIni         = Carbon::now()->locale('id')->isoFormat('dddd'); // Contoh: "Senin"

        // 3. Cari Tahun Ajar yang Aktif (Asumsi ada kolom status = 'aktif' atau '1')
        $tahunAjarAktif = TahunAjar::where('status', 'aktif')->first();

        if (!$tahunAjarAktif) {
            return back()->with('error', 'Tahun ajar aktif tidak ditemukan! Harap setting tahun ajar terlebih dahulu.');
        }

        // 4. Cari Jadwal Pelajaran yang sedang berlangsung
        // Logika: Cari jadwal di hari ini, dimana jam sekarang berada di antara jam mulai dan selesai
        $jadwalAktif = RombelJadwalPelajaran::where('hari', $hariIni)
                        ->where('jam_mulai', '<=', $jamSekarang)
                        ->where('jam_selesai', '>=', $jamSekarang)
                        // Opsional: Filter berdasarkan kelas siswa jika perlu
                        // ->where('id_kelas', $siswa->id_kelas) 
                        ->first();

        // Fallback: Jika admin input manual di luar jam pelajaran, kita bisa set null (jika db nullable) 
        // atau ambil jadwal default/dummy. Di sini saya return error jika strict.
        if (!$jadwalAktif) {
             // OPSI A: Tolak jika tidak ada jadwal
             return back()->with('error', "Tidak ada jadwal pelajaran aktif pada hari $hariIni jam $jamSekarang.");
             
             // OPSI B (Alternatif): Jika ingin tetap simpan walau tidak ada jadwal (misal kegiatan ekskul)
             // $id_jadwal = 1; // ID jadwal dummy/umum
        }

        // 5. Simpan Data
        Presensi::create([
            'id_siswa'                   => $request->id_siswa,
            'id_rombel_jadwal_pelajaran' => $jadwalAktif->id, // Ambil ID dari hasil pencarian di atas
            'tanggal'                    => $tanggalSekarang,
            'jam_scan'                   => $jamSekarang,
            'id_device'                  => $request->id_device,
            'status'                     => $request->status,
            'id_tahun_ajar'              => $tahunAjarAktif->id,
        ]);

        return redirect()->route('admin.presensi.index')->with('success', 'Data absensi berhasil disimpan.');
    }
}