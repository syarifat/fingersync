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

        $kelas = $query->latest()->paginate(10);
        return view('admin.kelas.index', compact('kelas', 'jurusan'));
    }

    public function create()
    {
        $jurusan = Jurusan::all();
        return view('admin.kelas.create', compact('jurusan'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|unique:kelas,nama',
            'id_jurusan' => 'required|exists:jurusan,id',
        ]);

        Kelas::create($request->all());

        return redirect()->route('admin.kelas.index')->with('success', 'Kelas baru berhasil dibuat.');
    }

    public function edit(Kelas $kela) // Laravel resource defaultnya $kela untuk singular Kelas
    {
        $jurusan = Jurusan::all();
        return view('admin.kelas.edit', ['kelas' => $kela, 'jurusan' => $jurusan]);
    }

    public function update(Request $request, Kelas $kela)
    {
        $request->validate([
            'nama' => 'required|unique:kelas,nama,' . $kela->id,
            'id_jurusan' => 'required|exists:jurusan,id',
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
