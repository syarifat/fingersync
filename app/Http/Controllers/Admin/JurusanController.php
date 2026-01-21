<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Jurusan;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class JurusanController extends Controller
{
    public function index()
    {
        $jurusan = Jurusan::latest()->paginate(10);
        return view('admin.jurusan.index', compact('jurusan'));
    }

    public function create()
    {
        return view('admin.jurusan.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255|unique:jurusan,nama',
        ]);

        Jurusan::create($request->all());

        return redirect()->route('admin.jurusan.index')->with('success', 'Jurusan baru berhasil ditambahkan.');
    }

    public function edit(Jurusan $jurusan)
    {
        return view('admin.jurusan.edit', compact('jurusan'));
    }

    public function update(Request $request, Jurusan $jurusan)
    {
        $request->validate([
            'nama' => ['required', 'string', 'max:255', Rule::unique('jurusan')->ignore($jurusan->id)],
        ]);

        $jurusan->update($request->all());

        return redirect()->route('admin.jurusan.index')->with('success', 'Nama jurusan berhasil diperbarui.');
    }

    public function destroy(Jurusan $jurusan)
    {
        // Cek apakah jurusan dipakai di Kelas atau Siswa
        if ($jurusan->kelas()->exists() || $jurusan->siswa()->exists()) {
            return back()->with('error', 'Gagal hapus! Jurusan ini masih digunakan oleh Kelas atau Siswa.');
        }

        $jurusan->delete();
        return redirect()->route('admin.jurusan.index')->with('success', 'Jurusan berhasil dihapus.');
    }
}