<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ruangan;
use Illuminate\Http\Request;

class RuanganController extends Controller
{
    public function index(Request $request)
    {
        $query = Ruangan::query();

        if ($request->has('search') && $request->search != '') {
            $query->where('nama_ruangan', 'like', '%' . $request->search . '%');
        }

        // Urutkan berdasarkan nama ruangan
        $ruangan = $query->orderBy('nama_ruangan', 'asc')->paginate(10);
        return view('admin.ruangan.index', compact('ruangan'));
    }

    public function create()
    {
        return view('admin.ruangan.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_ruangan' => 'required|string|max:255|unique:ruangan,nama_ruangan',
            'keterangan' => 'nullable|string|max:255',
        ]);

        Ruangan::create($request->all());

        return redirect()->route('admin.ruangan.index')->with('success', 'Ruangan baru berhasil ditambahkan.');
    }

    public function edit(Ruangan $ruangan)
    {
        return view('admin.ruangan.edit', compact('ruangan'));
    }

    public function update(Request $request, Ruangan $ruangan)
    {
        $request->validate([
            'nama_ruangan' => 'required|string|max:255|unique:ruangan,nama_ruangan,' . $ruangan->id,
            'keterangan' => 'nullable|string|max:255',
        ]);

        $ruangan->update($request->all());

        return redirect()->route('admin.ruangan.index')->with('success', 'Data ruangan berhasil diperbarui.');
    }

    public function destroy(Ruangan $ruangan)
    {
        // Cek apakah ada device di ruangan ini sebelum hapus (Opsional, untuk keamanan)
        if ($ruangan->devices()->count() > 0) {
            return back()->with('error', 'Gagal hapus! Masih ada perangkat IoT yang terdaftar di ruangan ini.');
        }

        $ruangan->delete();
        return redirect()->route('admin.ruangan.index')->with('success', 'Ruangan berhasil dihapus.');
    }
}
