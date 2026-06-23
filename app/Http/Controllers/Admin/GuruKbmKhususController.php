<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GuruKbmKhusus;
use App\Models\Guru;
use App\Models\RombelJadwalPelajaran;
use Illuminate\Http\Request;

class GuruKbmKhususController extends Controller
{
    public function index()
    {
        $kbmKhususList = GuruKbmKhusus::with(['rombelJadwalPelajaran.rombelMapel.kelas', 'rombelJadwalPelajaran.rombelMapel.mataPelajaran', 'rombelJadwalPelajaran.rombelMapel.guru', 'guruPengganti'])
            ->orderBy('tanggal', 'desc')
            ->get();
        return view('admin.kbm-khusus.index', compact('kbmKhususList'));
    }

    public function create()
    {
        $jadwals = RombelJadwalPelajaran::with(['rombelMapel.kelas', 'rombelMapel.mataPelajaran', 'rombelMapel.guru', 'ruangan'])
            ->get();
        return view('admin.kbm-khusus.create', compact('jadwals'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_rombel_jadwal_pelajaran' => 'required|exists:rombel_jadwal_pelajaran,id',
            'tanggal' => 'required|date',
            'status' => 'required|in:izin_tugas,izin_libur',
            'keterangan' => 'nullable|string',
        ]);

        $jadwal = RombelJadwalPelajaran::with('rombelMapel')->find($request->id_rombel_jadwal_pelajaran);
        if ($jadwal) {
            $hariIndo = $this->getHariIndo(\Carbon\Carbon::parse($request->tanggal)->format('l'));
            if (strtolower($hariIndo) !== strtolower($jadwal->hari)) {
                return back()->withErrors(['tanggal' => "Tanggal berhalangan harus bertepatan dengan hari {$jadwal->hari} (jadwal pelajaran)."])->withInput();
            }
        }

        $data = $request->only(['id_rombel_jadwal_pelajaran', 'tanggal', 'status', 'keterangan']);
        $data['id_guru_pengganti'] = null;

        GuruKbmKhusus::create($data);

        return redirect()->route('admin.kbm-khusus.index')->with('success', 'Kondisi KBM khusus guru berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $kbmKhusus = GuruKbmKhusus::findOrFail($id);
        $jadwals = RombelJadwalPelajaran::with(['rombelMapel.kelas', 'rombelMapel.mataPelajaran', 'rombelMapel.guru', 'ruangan'])
            ->get();
        return view('admin.kbm-khusus.edit', compact('kbmKhusus', 'jadwals'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'id_rombel_jadwal_pelajaran' => 'required|exists:rombel_jadwal_pelajaran,id',
            'tanggal' => 'required|date',
            'status' => 'required|in:izin_tugas,izin_libur',
            'keterangan' => 'nullable|string',
        ]);

        $jadwal = RombelJadwalPelajaran::with('rombelMapel')->find($request->id_rombel_jadwal_pelajaran);
        if ($jadwal) {
            $hariIndo = $this->getHariIndo(\Carbon\Carbon::parse($request->tanggal)->format('l'));
            if (strtolower($hariIndo) !== strtolower($jadwal->hari)) {
                return back()->withErrors(['tanggal' => "Tanggal berhalangan harus bertepatan dengan hari {$jadwal->hari} (jadwal pelajaran)."])->withInput();
            }
        }

        $kbmKhusus = GuruKbmKhusus::findOrFail($id);
        $data = $request->only(['id_rombel_jadwal_pelajaran', 'tanggal', 'status', 'keterangan']);
        $data['id_guru_pengganti'] = null;
        
        $kbmKhusus->update($data);

        return redirect()->route('admin.kbm-khusus.index')->with('success', 'Kondisi KBM khusus guru berhasil diperbarui.');
    }

    private function getHariIndo($day)
    {
        $days = [
            'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu', 'Sunday' => 'Minggu'
        ];
        return $days[$day] ?? 'Senin';
    }

    public function destroy($id)
    {
        $kbmKhusus = GuruKbmKhusus::findOrFail($id);
        $kbmKhusus->delete();

        return redirect()->route('admin.kbm-khusus.index')->with('success', 'Kondisi KBM khusus guru berhasil dihapus.');
    }
}
