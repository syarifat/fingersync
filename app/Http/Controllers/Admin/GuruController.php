<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\Rule;

class GuruController extends Controller
{
    /**
     * Menampilkan daftar guru
     */
    public function index(Request $request)
    {
        $query = Guru::query();

        // 1. Filter Pencarian (Nama atau NIDN)
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nama', 'LIKE', '%' . $search . '%')
                  ->orWhere('nidn', 'LIKE', '%' . $search . '%');
            });
        }

        // 2. Filter Status (Aktif/Nonaktif/Cuti/dll)
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        // 3. Filter Tipe Guru (BK atau Mapel Biasa)
        if ($request->has('is_bk') && $request->is_bk != '') {
            $query->where('is_bk', $request->is_bk);
        }

        // Eksekusi dengan Pagination
        $guru = $query->latest()->paginate(10)->withQueryString();

        return view('admin.guru.index', compact('guru'));
    }

    /**
     * Menampilkan form tambah guru
     */
    public function create()
    {
        return view('admin.guru.create');
    }

    /**
     * Menyimpan data guru baru (Password NULL)
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'nidn' => 'required|numeric|unique:guru,nidn',
            'gender' => 'required|in:Laki-laki,Perempuan',
            'nohp' => 'required|numeric',
            'username' => 'required|unique:users,username',
            'alamat' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        // 1. Buat User Login (Password NULL agar bisa Aktivasi)
        $user = User::create([
            'nama' => $request->nama,
            'username' => $request->username,
            'password' => null, // PENTING: Password Null
            'role' => 'guru',
        ]);

        // 2. Persiapkan Data Profil Guru
        $dataGuru = $request->except(['image', '_token']);
        $dataGuru['user_id'] = $user->id;
        $dataGuru['password'] = null; // Di tabel guru juga null
        $dataGuru['is_bk'] = $request->has('is_bk') ? 1 : 0;
        $dataGuru['status'] = 'Aktif';

        // 3. Handle Upload Foto
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            // Format nama file: nidn_nama.ext
            $nama_file = $request->nidn . '_' . str_replace(' ', '_', strtolower($request->nama)) . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('img/guru'), $nama_file);
            $dataGuru['image'] = $nama_file;
        }

        // 4. Simpan ke Tabel Guru
        Guru::create($dataGuru);

        return redirect()->route('admin.guru.index')
            ->with('success', 'Data Guru berhasil dibuat. Silakan informasikan Username kepada Guru untuk melakukan "Aktivasi Akun".');
    }

    /**
     * Menampilkan form edit guru
     */
    public function edit(Guru $guru)
    {
        return view('admin.guru.edit', compact('guru'));
    }

    /**
     * Memperbarui data guru
     */
    public function update(Request $request, Guru $guru)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'nidn' => ['required', Rule::unique('guru')->ignore($guru->id)],
            'username' => ['required', Rule::unique('users')->ignore($guru->user_id)],
            'gender' => 'required',
            'nohp' => 'required',
            'alamat' => 'required',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        // 1. Update Data User (Login Info)
        $user = User::find($guru->user_id);
        $user->update([
            'nama' => $request->nama,
            'username' => $request->username,
            // Password tidak diubah disini
        ]);

        // 2. Update Data Profil Guru
        $guru->nama = $request->nama;
        $guru->nidn = $request->nidn;
        $guru->gender = $request->gender;
        $guru->alamat = $request->alamat;
        $guru->nohp = $request->nohp;
        $guru->username = $request->username;
        $guru->is_bk = $request->has('is_bk') ? 1 : 0;
        $guru->status = $request->status;

        // 3. Handle Ganti Foto
        if ($request->hasFile('image')) {
            // Hapus foto lama jika ada
            if ($guru->image && File::exists(public_path('img/guru/' . $guru->image))) {
                File::delete(public_path('img/guru/' . $guru->image));
            }
            
            $file = $request->file('image');
            $nama_file = $request->nidn . '_' . str_replace(' ', '_', strtolower($request->nama)) . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('img/guru'), $nama_file);
            $guru->image = $nama_file;
        }

        $guru->save();

        return redirect()->route('admin.guru.index')->with('success', 'Data profil Guru berhasil diperbarui.');
    }

    /**
     * Menghapus data guru & user
     */
    public function destroy(Guru $guru)
    {
        // 1. Hapus Foto Fisik
        if ($guru->image && File::exists(public_path('img/guru/' . $guru->image))) {
            File::delete(public_path('img/guru/' . $guru->image));
        }

        // 2. Hapus User (Otomatis hapus guru karena cascade delete di database)
        $user = User::find($guru->user_id);
        if ($user) {
            $user->delete();
        } else {
            $guru->delete();
        }

        return redirect()->route('admin.guru.index')->with('success', 'Data Guru dan Akun Login telah dihapus permanen.');
    }
}