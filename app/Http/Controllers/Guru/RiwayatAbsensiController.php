<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\RombelJadwalPelajaran;
use App\Models\RombelKelas;
use App\Models\RombelMataPelajaran;
use App\Models\Kelas;
use App\Models\Presensi;
use App\Models\Device;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class RiwayatAbsensiController extends Controller
{
    // 1. HALAMAN INDEX: Menampilkan Filter & Kartu Jadwal
    public function index(Request $request)
    {
        $guru = Guru::where('user_id', Auth::id())->firstOrFail();

        $tanggalFilter = $request->tanggal ?? Carbon::now()->format('Y-m-d');
        $kelasFilter = $request->kelas_id ?? '';

        Carbon::setLocale('id');
        $hariFilter = Carbon::parse($tanggalFilter)->isoFormat('dddd');

        $guru_id = $guru->id;

        // Ambil ID jadwal di mana guru ini adalah guru pengganti pada tanggal tersebut
        $idJadwalPengganti = \App\Models\GuruKbmKhusus::whereDate('tanggal', $tanggalFilter)
            ->where('status', 'diganti')
            ->where('id_guru_pengganti', $guru_id)
            ->pluck('id_rombel_jadwal_pelajaran')
            ->toArray();

        // Ambil ID jadwal di mana guru ini izin/absen/diganti (tidak aktif mengajar) pada tanggal tersebut
        $idJadwalNonAktif = \App\Models\GuruKbmKhusus::whereDate('tanggal', $tanggalFilter)
            ->whereIn('status', ['izin', 'absen', 'diganti'])
            ->pluck('id_rombel_jadwal_pelajaran')
            ->toArray();

        // Ambil kelas untuk Dropdown (termasuk kelas pengganti)
        $kelasIdsYangDiajar = RombelMataPelajaran::where('id_guru', $guru_id)->pluck('id_kelas')->toArray();
        $kelasIdsPengganti = RombelJadwalPelajaran::whereIn('id', $idJadwalPengganti)
            ->get()
            ->map(fn($j) => $j->rombelMataPelajaran->id_kelas ?? null)
            ->filter()
            ->toArray();
        $kelasIdsAll = array_unique(array_merge($kelasIdsYangDiajar, $kelasIdsPengganti));
        $kelasList = Kelas::whereIn('id', $kelasIdsAll)->orderBy('nama', 'asc')->get();

        // Cari Jadwal Mengajar reguler minus yang dinonaktifkan
        $query = RombelJadwalPelajaran::with(['rombelMataPelajaran.kelas', 'rombelMataPelajaran.mataPelajaran', 'ruangan'])
            ->where(function($q) use ($guru_id, $hariFilter, $idJadwalNonAktif) {
                $q->whereHas('rombelMataPelajaran', function ($sub) use ($guru_id) {
                    $sub->where('id_guru', $guru_id);
                })
                ->where('hari', $hariFilter)
                ->whereNotIn('id', $idJadwalNonAktif);
            });

        // Gabungkan jadwal di mana dia menjadi guru pengganti
        if (!empty($idJadwalPengganti)) {
            $query->orWhereIn('id', $idJadwalPengganti);
        }

        if ($kelasFilter != '') {
            $query->whereHas('rombelMataPelajaran', function ($q) use ($kelasFilter) {
                $q->where('id_kelas', $kelasFilter);
            });
        }

        $jadwalList = $query->orderBy('jam_mulai', 'asc')->get();

        return view('guru.riwayat-absensi.index', compact('tanggalFilter', 'hariFilter', 'kelasFilter', 'kelasList', 'jadwalList'));
    }

    // 2. HALAMAN SHOW: Menampilkan form absensi di tanggal tersebut
    public function show(Request $request, $id_jadwal)
    {
        $tanggalFilter = $request->tanggal ?? Carbon::now()->format('Y-m-d');
        
        $jadwal = RombelJadwalPelajaran::with(['rombelMataPelajaran.kelas', 'rombelMataPelajaran.mataPelajaran'])
            ->findOrFail($id_jadwal);

        $guru_id = Guru::where('user_id', Auth::id())->value('id');
        
        $isSubstitute = \App\Models\GuruKbmKhusus::where('id_rombel_jadwal_pelajaran', $id_jadwal)
            ->whereDate('tanggal', $tanggalFilter)
            ->where('status', 'diganti')
            ->where('id_guru_pengganti', $guru_id)
            ->exists();

        if ($jadwal->rombelMataPelajaran->id_guru != $guru_id && !$isSubstitute) {
            abort(403, 'Akses Ditolak.');
        }

        $rombelSiswa = RombelKelas::with('siswa')
            ->where('id_kelas', $jadwal->rombelMataPelajaran->id_kelas)
            ->get();

        $presensiHariIni = Presensi::where('id_rombel_jadwal_pelajaran', $id_jadwal)
            ->where('tanggal', $tanggalFilter)
            ->get()
            ->keyBy('id_siswa'); 

        return view('guru.riwayat-absensi.show', compact('jadwal', 'rombelSiswa', 'presensiHariIni', 'tanggalFilter'));
    }

    // 3. PROSES UPDATE: Menyimpan perubahan
    public function update(Request $request, $id_jadwal)
    {
        $tanggalFilter = $request->tanggal;
        $jadwal = RombelJadwalPelajaran::with('rombelMataPelajaran')->findOrFail($id_jadwal);
        
        $device = Device::first();
        if(!$device) return back()->with('error', 'Sistem butuh minimal 1 Device terdaftar untuk absen manual.');

        if ($request->has('status')) {
            foreach ($request->status as $id_siswa => $status) {
                if ($status == 'Belum Hadir') continue; 

                $presensi = Presensi::where('id_rombel_jadwal_pelajaran', $id_jadwal)
                    ->where('id_siswa', $id_siswa)
                    ->where('tanggal', $tanggalFilter)
                    ->first();

                if ($presensi) {
                    if($presensi->status != $status) {
                        $presensi->update(['status' => $status]);
                    }
                } else {
                    Presensi::create([
                        'id_siswa' => $id_siswa,
                        'id_rombel_jadwal_pelajaran' => $id_jadwal,
                        'tanggal' => $tanggalFilter,
                        'jam_scan' => Carbon::now()->format('H:i:s'),
                        'id_device' => $device->id,
                        'status' => $status,
                        'id_tahun_ajar' => $jadwal->rombelMataPelajaran->id_tahun_ajar,
                    ]);
                }
            }
        }

        return back()->with('success', "Status absensi untuk tanggal " . \Carbon\Carbon::parse($tanggalFilter)->isoFormat('DD MMMM YYYY') . " berhasil disimpan!");
    }
}