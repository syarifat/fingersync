<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Siswa;
use App\Models\Jurusan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File; // Penting untuk hapus file

class SiswaController extends Controller
{
    public function index()
    {
        $siswa = Siswa::with('jurusan')->latest()->paginate(10);
        return view('admin.siswa.index', compact('siswa'));
    }

    public function create()
    {
        $jurusan = Jurusan::all();
        return view('admin.siswa.create', compact('jurusan'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nis' => 'required|unique:siswa',
            'nama' => 'required',
            'id_jurusan' => 'required',
            'fingerprint_id' => 'required|numeric',
            'email' => 'nullable|email',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $data = $request->all();

        // Handle Upload Foto
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            // Format: nis_nama.ekstensi (spasi di nama diganti underscore)
            $nama_pria = str_replace(' ', '_', strtolower($request->nama));
            $nama_file = $request->nis . '_' . $nama_pria . '.' . $file->getClientOriginalExtension();
            
            $file->move(public_path('img/siswa'), $nama_file);
            $data['image'] = $nama_file;
        }

        Siswa::create($data);

        return redirect()->route('admin.siswa.index')->with('success', 'Data siswa berhasil ditambahkan.');
    }

    public function edit(Siswa $siswa)
    {
        $jurusan = Jurusan::all();
        return view('admin.siswa.edit', compact('siswa', 'jurusan'));
    }

    public function update(Request $request, Siswa $siswa)
    {
        $request->validate([
            'nis' => 'required|unique:siswa,nis,' . $siswa->id,
            'nama' => 'required',
            'id_jurusan' => 'required',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $data = $request->all();

        if ($request->hasFile('image')) {
            // 1. Hapus foto lama jika ada
            if ($siswa->image && File::exists(public_path('img/siswa/' . $siswa->image))) {
                File::delete(public_path('img/siswa/' . $siswa->image));
            }

            // 2. Upload foto baru
            $file = $request->file('image');
            $nama_pria = str_replace(' ', '_', strtolower($request->nama));
            $nama_file = $request->nis . '_' . $nama_pria . '.' . $file->getClientOriginalExtension();
            
            $file->move(public_path('img/siswa'), $nama_file);
            $data['image'] = $nama_file;
        }

        $siswa->update($data);

        return redirect()->route('admin.siswa.index')->with('success', 'Data siswa berhasil diperbarui.');
    }

    public function destroy(Siswa $siswa)
    {
        // Hapus foto dari folder sebelum hapus record
        if ($siswa->image && File::exists(public_path('img/siswa/' . $siswa->image))) {
            File::delete(public_path('img/siswa/' . $siswa->image));
        }

        $siswa->delete();
        return redirect()->route('admin.siswa.index')->with('success', 'Data siswa berhasil dihapus.');
    }
}