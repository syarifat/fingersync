<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TahunAjar;
use Illuminate\Http\Request;

class TahunAjarController extends Controller
{
    public function index()
    {
        // Urutkan tahun terbaru di atas
        $tahun_ajar = TahunAjar::orderBy('tahun', 'desc')->orderBy('semester', 'desc')->paginate(10);
        return view('admin.tahun-ajar.index', compact('tahun_ajar'));
    }

    public function create()
    {
        return view('admin.tahun-ajar.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'tahun' => 'required|string|max:20', // Contoh: 2025/2026
            'semester' => 'required|in:Ganjil,Genap',
        ]);

        $status = $request->has('status_aktif') ? true : false;

        // LOGIKA PENTING: Jika status di-set aktif, matikan yang lain
        if ($status) {
            TahunAjar::where('status_aktif', true)->update(['status_aktif' => false]);
        }

        TahunAjar::create([
            'tahun' => $request->tahun,
            'semester' => $request->semester,
            'status_aktif' => $status
        ]);

        return redirect()->route('admin.tahun-ajar.index')
            ->with('success', 'Tahun ajar baru berhasil ditambahkan.');
    }

    public function edit(TahunAjar $tahun_ajar)
    {
        return view('admin.tahun-ajar.edit', compact('tahun_ajar'));
    }

    public function update(Request $request, TahunAjar $tahun_ajar)
    {
        $request->validate([
            'tahun' => 'required|string|max:20',
            'semester' => 'required|in:Ganjil,Genap',
        ]);

        $status = $request->has('status_aktif') ? true : false;

        // LOGIKA PENTING: Jika status diubah jadi aktif, matikan yang lain
        if ($status && !$tahun_ajar->status_aktif) {
            TahunAjar::where('status_aktif', true)->update(['status_aktif' => false]);
        }
        
        // Mencegah mematikan satu-satunya tahun aktif tanpa pengganti (Opsional, tapi disarankan)
        if (!$status && $tahun_ajar->status_aktif) {
            // Jika user mencoba mematikan tahun aktif tanpa mengaktifkan yang lain,
            // Anda bisa menolak atau membiarkannya (tergantung kebijakan).
            // Di sini kita biarkan saja (sistem jadi tidak ada tahun aktif).
        }

        $tahun_ajar->update([
            'tahun' => $request->tahun,
            'semester' => $request->semester,
            'status_aktif' => $status
        ]);

        return redirect()->route('admin.tahun-ajar.index')
            ->with('success', 'Data tahun ajar berhasil diperbarui.');
    }

    public function destroy(TahunAjar $tahun_ajar)
    {
        if ($tahun_ajar->status_aktif) {
            return back()->with('error', 'Tidak bisa menghapus Tahun Ajar yang sedang AKTIF.');
        }

        $tahun_ajar->delete();
        return redirect()->route('admin.tahun-ajar.index')
            ->with('success', 'Tahun ajar berhasil dihapus.');
    }
}