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
        $gurus = Guru::where('status', 'Aktif')->orderBy('nama', 'asc')->get();
        $jadwals = RombelJadwalPelajaran::with(['rombelMapel.kelas', 'rombelMapel.mataPelajaran', 'rombelMapel.guru', 'ruangan'])
            ->get();
        return view('admin.kbm-khusus.create', compact('jadwals', 'gurus'));
    }

    public function store(Request $request)
    {
        $type = $request->input('input_type', 'single');

        if ($type === 'bulk') {
            $request->validate([
                'tanggal_mulai' => 'required|date',
                'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
                'schedules' => 'required|array',
                'schedules.*' => 'exists:rombel_jadwal_pelajaran,id',
                'status' => 'required|in:izin_tugas,izin_libur',
                'keterangan' => 'nullable|string',
            ]);

            $start = \Carbon\Carbon::parse($request->tanggal_mulai);
            $end = \Carbon\Carbon::parse($request->tanggal_selesai);
            $schedules = RombelJadwalPelajaran::whereIn('id', $request->schedules)->get();
            
            $createdCount = 0;
            $current = $start->copy();

            while ($current->lte($end)) {
                $tanggalStr = $current->format('Y-m-d');
                $hariIndo = $this->getHariIndo($current->format('l'));
                
                $matchingSchedules = $schedules->where('hari', $hariIndo);
                
                foreach ($matchingSchedules as $jdwl) {
                    $exists = GuruKbmKhusus::where('id_rombel_jadwal_pelajaran', $jdwl->id)
                        ->whereDate('tanggal', $tanggalStr)
                        ->exists();
                        
                    if (!$exists) {
                        GuruKbmKhusus::create([
                            'id_rombel_jadwal_pelajaran' => $jdwl->id,
                            'tanggal' => $tanggalStr,
                            'status' => $request->status,
                            'keterangan' => $request->keterangan,
                            'id_guru_pengganti' => null,
                        ]);
                        $createdCount++;
                    }
                }
                $current->addDay();
            }

            return redirect()->route('admin.kbm-khusus.index')
                ->with('success', "Kondisi KBM khusus guru berhasil ditambahkan bulk ({$createdCount} jadwal dibuat).");

        } else {
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
