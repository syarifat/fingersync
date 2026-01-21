<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MataPelajaran;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MataPelajaranController extends Controller
{
    public function index()
    {
        $mapel = MataPelajaran::orderBy('nama', 'asc')->paginate(10);
        return view('admin.mata-pelajaran.index', compact('mapel'));
    }

    public function create()
    {
        return view('admin.mata-pelajaran.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255|unique:mata_pelajaran,nama',
        ]);

        MataPelajaran::create($request->all());

        return redirect()->route('admin.mata-pelajaran.index')
            ->with('success', 'Mata Pelajaran berhasil ditambahkan.');
    }

    public function edit(MataPelajaran $mata_pelajaran)
    {
        return view('admin.mata-pelajaran.edit', compact('mata_pelajaran'));
    }

    public function update(Request $request, MataPelajaran $mata_pelajaran)
    {
        $request->validate([
            'nama' => ['required', 'string', 'max:255', Rule::unique('mata_pelajaran')->ignore($mata_pelajaran->id)],
        ]);

        $mata_pelajaran->update($request->all());

        return redirect()->route('admin.mata-pelajaran.index')
            ->with('success', 'Nama mata pelajaran berhasil diperbarui.');
    }

    public function destroy(MataPelajaran $mata_pelajaran)
    {
        // Cek apakah mapel sudah dipakai di jadwal (Rombel) - Opsional, jika model RombelMataPelajaran belum ada, bisa dikomentari dulu
        /* if ($mata_pelajaran->rombelMataPelajaran()->exists()) {
             return back()->with('error', 'Gagal hapus! Mapel ini sedang digunakan dalam jadwal aktif.');
        } 
        */

        $mata_pelajaran->delete();
        return redirect()->route('admin.mata-pelajaran.index')
            ->with('success', 'Mata pelajaran berhasil dihapus.');
    }
}