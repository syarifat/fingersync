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
        $gurus = Guru::orderBy('nama', 'asc')->get();
        $jadwals = RombelJadwalPelajaran::with(['rombelMapel.kelas', 'rombelMapel.mataPelajaran', 'rombelMapel.guru', 'ruangan'])
            ->get();
        return view('admin.kbm-khusus.create', compact('gurus', 'jadwals'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_rombel_jadwal_pelajaran' => 'required|exists:rombel_jadwal_pelajaran,id',
            'tanggal' => 'required|date',
            'status' => 'required|in:izin,absen,diganti',
            'id_guru_pengganti' => 'nullable|required_if:status,diganti|exists:guru,id',
            'keterangan' => 'nullable|string',
        ]);

        // Cek guru pengganti tidak boleh sama dengan guru asli
        if ($request->status === 'diganti') {
            $jadwal = RombelJadwalPelajaran::with('rombelMapel')->find($request->id_rombel_jadwal_pelajaran);
            if ($jadwal && $jadwal->rombelMapel->id_guru == $request->id_guru_pengganti) {
                return back()->withErrors(['id_guru_pengganti' => 'Guru pengganti tidak boleh sama dengan guru mata pelajaran tersebut.'])->withInput();
            }
        }

        GuruKbmKhusus::create($request->all());

        return redirect()->route('admin.kbm-khusus.index')->with('success', 'Kondisi KBM khusus guru berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $kbmKhusus = GuruKbmKhusus::findOrFail($id);
        $gurus = Guru::orderBy('nama', 'asc')->get();
        $jadwals = RombelJadwalPelajaran::with(['rombelMapel.kelas', 'rombelMapel.mataPelajaran', 'rombelMapel.guru', 'ruangan'])
            ->get();
        return view('admin.kbm-khusus.edit', compact('kbmKhusus', 'gurus', 'jadwals'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'id_rombel_jadwal_pelajaran' => 'required|exists:rombel_jadwal_pelajaran,id',
            'tanggal' => 'required|date',
            'status' => 'required|in:izin,absen,diganti',
            'id_guru_pengganti' => 'nullable|required_if:status,diganti|exists:guru,id',
            'keterangan' => 'nullable|string',
        ]);

        if ($request->status === 'diganti') {
            $jadwal = RombelJadwalPelajaran::with('rombelMapel')->find($request->id_rombel_jadwal_pelajaran);
            if ($jadwal && $jadwal->rombelMapel->id_guru == $request->id_guru_pengganti) {
                return back()->withErrors(['id_guru_pengganti' => 'Guru pengganti tidak boleh sama dengan guru mata pelajaran tersebut.'])->withInput();
            }
        }

        $kbmKhusus = GuruKbmKhusus::findOrFail($id);
        $kbmKhusus->update($request->all());

        return redirect()->route('admin.kbm-khusus.index')->with('success', 'Kondisi KBM khusus guru berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $kbmKhusus = GuruKbmKhusus::findOrFail($id);
        $kbmKhusus->delete();

        return redirect()->route('admin.kbm-khusus.index')->with('success', 'Kondisi KBM khusus guru berhasil dihapus.');
    }
}
