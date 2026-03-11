<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RombelKelas; // Model Baru
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\Guru;
use Illuminate\Http\Request;

class RombelKelasController extends Controller
{
    public function index(Request $request)
    {
        $activeYear = session('tahun_ajar_id');
        $kelas = Kelas::all(); // Untuk dropdown filter

        $query = RombelKelas::with(['kelas', 'siswa', 'waliKelas', 'guruBk'])
            ->where('id_tahun_ajar', $activeYear);

        // Filter berdasarkan Kelas
        if ($request->has('kelas_id') && $request->kelas_id != '') {
            $query->where('id_kelas', $request->kelas_id);
        }

        // Filter pencarian (Nama Siswa atau NIS)
        if ($request->has('search') && $request->search != '') {
            $query->whereHas('siswa', function ($q) use ($request) {
                $q->where('nama', 'like', '%' . $request->search . '%')
                    ->orWhere('nis', 'like', '%' . $request->search . '%');
            });
        }

        $rombel = $query->latest()->paginate(10);

        return view('admin.rombel-kelas.index', compact('rombel', 'kelas'));
    }

    public function create()
    {
        $activeYear = session('tahun_ajar_id');

        $kelas = Kelas::all();
        $guru = Guru::where('status', 'Aktif')->get();

        // Cari siswa yang BELUM punya kelas di tahun ini
        // Kita gunakan nama model RombelKelas di sini
        $siswa = Siswa::whereDoesntHave('rombelKelas', function ($q) use ($activeYear) {
            $q->where('id_tahun_ajar', $activeYear);
        })->orderBy('nama', 'asc')->get();

        return view('admin.rombel-kelas.create', compact('kelas', 'guru', 'siswa'));
    }

    public function store(Request $request)
    {
        // 1. Validasi: id_siswa sekarang berupa array
        $request->validate([
            'id_kelas' => 'required|exists:kelas,id',
            'id_siswa' => 'required|array', // Pastikan inputnya array
            'id_siswa.*' => 'exists:siswa,id', // Pastikan setiap ID siswa valid
            'id_guru_wali_kelas' => 'required|exists:guru,id',
            'id_guru_bk' => 'required|exists:guru,id',
        ]);

        if (!session('tahun_ajar_id')) {
            return back()->with('error', 'Tahun Ajar belum dipilih!');
        }

        $tahunAjarId = session('tahun_ajar_id');
        $berhasil = 0;
        $gagal = 0;

        // 2. Looping: Simpan setiap siswa yang dipilih ke database
        foreach ($request->id_siswa as $siswaId) {
            // Cek duplikasi untuk masing-masing siswa (Jaga-jaga jika ada error/bypass)
            $exists = RombelKelas::where('id_siswa', $siswaId)
                ->where('id_tahun_ajar', $tahunAjarId)
                ->exists();

            if (!$exists) {
                RombelKelas::create([
                    'id_tahun_ajar' => $tahunAjarId,
                    'id_kelas' => $request->id_kelas,
                    'id_siswa' => $siswaId,
                    'id_guru_wali_kelas' => $request->id_guru_wali_kelas,
                    'id_guru_bk' => $request->id_guru_bk,
                ]);
                $berhasil++;
            } else {
                $gagal++;
            }
        }

        // 3. Buat pesan sukses yang informatif
        $pesan = "Berhasil menempatkan $berhasil siswa ke dalam kelas.";
        if ($gagal > 0) {
            $pesan .= " ($gagal siswa dilewati karena sudah memiliki kelas).";
        }

        return redirect()->route('admin.rombel-kelas.index')->with('success', $pesan);
    }

    public function edit($id)
    {
        $rombel = RombelKelas::findOrFail($id);
        $kelas = Kelas::all();
        $guru = Guru::where('status', 'Aktif')->get();

        return view('admin.rombel-kelas.edit', compact('rombel', 'kelas', 'guru'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'id_kelas' => 'required',
            'id_guru_wali_kelas' => 'required',
            'id_guru_bk' => 'required',
        ]);

        $rombel = RombelKelas::findOrFail($id);

        $rombel->update([
            'id_kelas' => $request->id_kelas,
            'id_guru_wali_kelas' => $request->id_guru_wali_kelas,
            'id_guru_bk' => $request->id_guru_bk,
        ]);

        return redirect()->route('admin.rombel-kelas.index')->with('success', 'Data penempatan kelas berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $rombel = RombelKelas::findOrFail($id);
        $rombel->delete();
        return redirect()->route('admin.rombel-kelas.index')->with('success', 'Siswa dikeluarkan dari kelas.');
    }
}
