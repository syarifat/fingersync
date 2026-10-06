<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KegiatanSekolah;
use Illuminate\Http\Request;

class KegiatanSekolahController extends Controller
{
    public function index()
    {
        $kegiatanList = KegiatanSekolah::orderBy('tanggal', 'desc')->get();
        return view('admin.kegiatan-sekolah.index', compact('kegiatanList'));
    }

    public function create()
    {
        return view('admin.kegiatan-sekolah.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_kegiatan' => 'required|string|max:255',
            'tanggal' => 'required|date',
            'tipe' => 'required|in:serentak,non-serentak',
            'jam_mulai_datang' => 'required',
            'jam_selesai_datang' => 'required',
            'jam_mulai_pulang' => 'required',
            'jam_selesai_pulang' => 'required',
            'keterangan' => 'nullable|string',
        ]);

        KegiatanSekolah::create($request->all());

        return redirect()->route('admin.kegiatan-sekolah.index')->with('success', 'Kegiatan sekolah berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $kegiatan = KegiatanSekolah::findOrFail($id);
        return view('admin.kegiatan-sekolah.edit', compact('kegiatan'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_kegiatan' => 'required|string|max:255',
            'tanggal' => 'required|date',
            'tipe' => 'required|in:serentak,non-serentak',
            'jam_mulai_datang' => 'required',
            'jam_selesai_datang' => 'required',
            'jam_mulai_pulang' => 'required',
            'jam_selesai_pulang' => 'required',
            'keterangan' => 'nullable|string',
        ]);

        $kegiatan = KegiatanSekolah::findOrFail($id);
        $kegiatan->update($request->all());

        return redirect()->route('admin.kegiatan-sekolah.index')->with('success', 'Kegiatan sekolah berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $kegiatan = KegiatanSekolah::findOrFail($id);
        $kegiatan->delete();

        return redirect()->route('admin.kegiatan-sekolah.index')->with('success', 'Kegiatan sekolah berhasil dihapus.');
    }
}
