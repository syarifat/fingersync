<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\Jurusan;
use Illuminate\Http\Request;

class KelasController extends Controller
{
    public function index(Request $request)
    {
        $jurusan = Jurusan::all();
        $query = Kelas::with('jurusan');

        if ($request->has('search') && $request->search != '') {
            $query->where('nama', 'like', '%' . $request->search . '%');
        }

        if ($request->has('id_jurusan') && $request->id_jurusan != '') {
            $query->where('id_jurusan', $request->id_jurusan);
        }

        $kelas = $query->latest()->paginate(10)->withQueryString();
        return view('admin.kelas.index', compact('kelas', 'jurusan'));
    }

    public function create()
    {
        $jurusan = Jurusan::all();
        return view('admin.kelas.create', compact('jurusan'));
    }

    public function store(Request $request)
    {
        // 1. Validasi dasar
        $request->validate([
            'nama' => 'required|string', // Karena sekarang berupa teks panjang dari textarea
            'id_jurusan' => 'required|exists:jurusan,id',
        ]);

        // 2. Pecah string berdasarkan enter (\n)
        // Menghapus \r (carriage return) untuk mencegah error format di OS Windows
        $barisKelas = explode("\n", str_replace("\r", "", $request->nama));
        
        $berhasil = 0;
        $dilewati = [];

        // 3. Looping untuk menyimpan setiap baris
        foreach ($barisKelas as $nama) {
            $namaBersih = trim($nama); // Hapus spasi berlebih di awal/akhir

            // Jika barisnya kosong (misal tertekan enter 2x), lewati
            if (empty($namaBersih)) {
                continue;
            }

            // Pengecekan agar tidak error jika ada nama kelas yang duplikat di DB
            $sudahAda = Kelas::where('nama', $namaBersih)->exists();

            if (!$sudahAda) {
                Kelas::create([
                    'nama' => $namaBersih,
                    'id_jurusan' => $request->id_jurusan
                ]);
                $berhasil++;
            } else {
                $dilewati[] = $namaBersih;
            }
        }

        // 4. Siapkan pesan sukses/info
        $pesan = "Berhasil menambahkan $berhasil kelas baru.";
        if (count($dilewati) > 0) {
            $pesan .= " Beberapa kelas dilewati karena sudah ada: " . implode(', ', $dilewati);
        }

        return redirect()->route('admin.kelas.index')->with('success', $pesan);
    }

    public function edit(Kelas $kela) // Laravel resource defaultnya $kela untuk singular Kelas
    {
        $jurusan = Jurusan::all();
        $waGroups = \App\Services\WhatsAppService::getGroups();
        return view('admin.kelas.edit', [
            'kelas' => $kela,
            'jurusan' => $jurusan,
            'waGroups' => $waGroups
        ]);
    }

    public function update(Request $request, Kelas $kela)
    {
        $request->validate([
            'nama' => 'required|unique:kelas,nama,' . $kela->id,
            'id_jurusan' => 'required|exists:jurusan,id',
            'id_grup_wa' => 'nullable|string',
        ]);

        $kela->update($request->all());

        return redirect()->route('admin.kelas.index')->with('success', 'Data kelas berhasil diperbarui.');
    }

    public function destroy(Kelas $kela)
    {
        $kela->delete();
        return redirect()->route('admin.kelas.index')->with('success', 'Kelas berhasil dihapus.');
    }
}
