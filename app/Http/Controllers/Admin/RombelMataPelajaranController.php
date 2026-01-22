<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RombelMataPelajaran;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\Guru;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;

class RombelMataPelajaranController extends Controller
{
    public function index()
    {
        $activeYear = session('tahun_ajar_id');

        // Ambil data plotting guru sesuai tahun aktif
        $rombelMapel = RombelMataPelajaran::with(['kelas', 'mataPelajaran', 'guru'])
                    ->where('id_tahun_ajar', $activeYear)
                    ->orderBy('id_kelas') 
                    ->orderBy('id_mata_pelajaran')
                    ->paginate(15);

        return view('admin.rombel-mata-pelajaran.index', compact('rombelMapel'));
    }

    public function create()
    {
        $kelas = Kelas::orderBy('nama', 'asc')->get();
        $mapel = MataPelajaran::orderBy('nama', 'asc')->get();
        $guru = Guru::where('status', 'Aktif')->orderBy('nama', 'asc')->get();

        return view('admin.rombel-mata-pelajaran.create', compact('kelas', 'mapel', 'guru'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_kelas' => 'required',
            'id_mata_pelajaran' => 'required',
            'id_guru' => 'required',
        ]);

        if (!session('tahun_ajar_id')) {
            return back()->with('error', 'Tahun Ajar belum dipilih!');
        }

        // Cek Duplikasi: Mapel yang sama di Kelas yang sama pada Tahun yang sama
        $exists = RombelMataPelajaran::where('id_tahun_ajar', session('tahun_ajar_id'))
                    ->where('id_kelas', $request->id_kelas)
                    ->where('id_mata_pelajaran', $request->id_mata_pelajaran)
                    ->exists();

        if ($exists) {
            return back()->with('error', 'Mata pelajaran ini sudah memiliki guru di kelas tersebut!');
        }

        RombelMataPelajaran::create([
            'id_tahun_ajar' => session('tahun_ajar_id'),
            'id_kelas' => $request->id_kelas,
            'id_mata_pelajaran' => $request->id_mata_pelajaran,
            'id_guru' => $request->id_guru,
        ]);

        return redirect()->route('admin.rombel-mata-pelajaran.index')->with('success', 'Plotting guru berhasil disimpan.');
    }

    public function edit($id)
    {
        $rombelMapel = RombelMataPelajaran::findOrFail($id);
        $kelas = Kelas::all();
        $mapel = MataPelajaran::all();
        $guru = Guru::where('status', 'Aktif')->get();

        return view('admin.rombel-mata-pelajaran.edit', compact('rombelMapel', 'kelas', 'mapel', 'guru'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'id_kelas' => 'required',
            'id_mata_pelajaran' => 'required',
            'id_guru' => 'required',
        ]);

        $rombelMapel = RombelMataPelajaran::findOrFail($id);
        
        $rombelMapel->update([
            'id_kelas' => $request->id_kelas,
            'id_mata_pelajaran' => $request->id_mata_pelajaran,
            'id_guru' => $request->id_guru,
        ]);

        return redirect()->route('admin.rombel-mata-pelajaran.index')->with('success', 'Data plotting guru berhasil diperbarui.');
    }

    public function destroy($id)
    {
        try {
            $rombelMapel = RombelMataPelajaran::findOrFail($id);
            $rombelMapel->delete();
            
            return redirect()->route('admin.rombel-mata-pelajaran.index')
                ->with('success', 'Data berhasil dihapus.');
                
        } catch (QueryException $e) {
            // Error 23000 adalah kode untuk Integrity Constraint Violation
            if ($e->getCode() == "23000") {
                return back()->with('error', 'Gagal Hapus! Data ini sudah digunakan di Jadwal Pelajaran atau memiliki Riwayat Presensi.');
            }
            
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}