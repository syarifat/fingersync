<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\RombelJadwalPelajaran;
use App\Models\RombelKelas;
use App\Models\Presensi;
use App\Models\Device;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class PresensiController extends Controller
{
    public function show($id)
    {
        // 1. Ambil data jadwal beserta relasinya (Gunakan rombelMapel sesuai revisi terbaru Anda)
        $jadwal = RombelJadwalPelajaran::with(['rombelMapel.kelas', 'rombelMapel.mataPelajaran'])
            ->findOrFail($id);

        // Keamanan: Pastikan jadwal ini milik guru yang login
        $guru_id = \App\Models\Guru::where('user_id', Auth::id())->value('id');
        if ($jadwal->rombelMapel->id_guru != $guru_id) {
            abort(403, 'Akses Ditolak. Ini bukan jadwal Anda.');
        }

        // 2. Ambil daftar siswa di kelas tersebut
        $rombelSiswa = RombelKelas::with('siswa')
            ->where('id_kelas', $jadwal->rombelMapel->id_kelas)
            ->where('id_tahun_ajar', $jadwal->rombelMapel->id_tahun_ajar)
            ->get();

        // 3. Ambil data presensi HARI INI
        $tanggalHariIni = Carbon::now()->format('Y-m-d');
        
        $presensiHariIni = Presensi::where('id_rombel_jadwal_pelajaran', $id)
            ->where('tanggal', $tanggalHariIni)
            ->get()
            ->keyBy('id_siswa'); 

        return view('guru.presensi.show', compact('jadwal', 'rombelSiswa', 'presensiHariIni', 'tanggalHariIni'));
    }

    public function update(Request $request, $id)
    {
        $tanggalHariIni = Carbon::now()->format('Y-m-d');
        $jadwal = RombelJadwalPelajaran::with('rombelMapel')->findOrFail($id);
        
        // Cari alat (Device) untuk absen manual. Jika tidak ada, tolak.
        $device = Device::first();
        if(!$device) {
            return back()->with('error', 'Sistem membutuhkan minimal 1 Device terdaftar untuk menyimpan presensi manual. Hubungi Admin.');
        }

        if ($request->has('status')) {
            foreach ($request->status as $id_siswa => $status) {
                // Abaikan jika statusnya masih "Belum Hadir" (Siswa belum datang / mesin belum nge-scan)
                if ($status == 'Belum Hadir') {
                    continue; 
                }

                $presensi = Presensi::where('id_rombel_jadwal_pelajaran', $id)
                    ->where('id_siswa', $id_siswa)
                    ->where('tanggal', $tanggalHariIni)
                    ->first();

                if ($presensi) {
                    // Jika data sudah ada (misal dari fingerprint), lalu guru mengubahnya
                    if($presensi->status != $status) {
                        $presensi->update(['status' => $status]);
                    }
                } else {
                    // Jika data belum ada (siswa absen manual: Izin/Sakit/Alpha)
                    Presensi::create([
                        'id_siswa' => $id_siswa,
                        'id_rombel_jadwal_pelajaran' => $id,
                        'tanggal' => $tanggalHariIni,
                        'jam_scan' => Carbon::now()->format('H:i:s'), // Waktu saat guru mengeklik simpan
                        'id_device' => $device->id,
                        'status' => $status,
                        'id_tahun_ajar' => $jadwal->rombelMapel->id_tahun_ajar,
                    ]);
                }
            }
        }

        return back()->with('success', 'Status absensi kelas berhasil diperbarui!');
    }
}