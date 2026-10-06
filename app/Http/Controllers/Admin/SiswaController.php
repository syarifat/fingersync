<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Siswa;
use App\Models\Jurusan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use App\Models\FingerprintInbox;
use App\Models\DeviceTask;
use App\Models\Device;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SiswaTemplateExport;
use App\Imports\SiswaImport;

class SiswaController extends Controller
{
    public function index(Request $request)
    {
        // 1. Ambil data jurusan untuk dropdown filter
        $jurusan = Jurusan::all();

        // 2. Mulai Query Siswa
        $query = Siswa::with('jurusan');

        // 3. Filter Search (Nama atau NIS)
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nama', 'LIKE', '%' . $search . '%')
                  ->orWhere('nis', 'LIKE', '%' . $search . '%');
            });
        }

        // 4. Filter Dropdown Jurusan
        if ($request->has('id_jurusan') && $request->id_jurusan != '') {
            $query->where('id_jurusan', $request->id_jurusan);
        }

        // 5. Eksekusi query + Pagination
        $siswa = $query->latest()->paginate(10)->withQueryString();

        return view('admin.siswa.index', compact('siswa', 'jurusan'));
    }

    // ==========================================
    // FITUR BARU: INBOX REGISTRASI JARI
    // ==========================================
    public function inbox()
    {
        // Ambil data jari yang masuk dari alat dan belum didaftarkan
        $inbox = FingerprintInbox::where('status', 'pending')->latest()->paginate(10);
        return view('admin.siswa.inbox', compact('inbox'));
    }

    // ==========================================
    // MODIFIKASI: CREATE (AUTO-FILL ID)
    // ==========================================
    public function create(Request $request)
    {
        $jurusan = Jurusan::all();
        
        // Tangkap parameter dari URL jika admin datang dari halaman Inbox
        $prefill_finger_id = $request->query('finger_id', '');
        $inbox_id = $request->query('inbox_id', '');
        
        return view('admin.siswa.create', compact('jurusan', 'prefill_finger_id', 'inbox_id'));
    }

public function store(Request $request)
    {
        $request->validate([
            'nis' => 'required|unique:siswa,nis', // Ditambahkan ,nis agar lebih spesifik
            'nama' => 'required',
            'id_jurusan' => 'required',
            'fingerprint_id' => 'required|numeric|unique:siswa,fingerprint_id', // PENTING: Mencegah ID jari ganda
            'email' => 'nullable|email',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $data = $request->all();

        // Handle Upload Foto
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            
            // Membersihkan nama dari spasi agar rapi saat jadi nama file
            $nama_clean = str_replace(' ', '_', strtolower($request->nama));
            $nama_file = $request->nis . '_' . $nama_clean . '.' . $file->getClientOriginalExtension();
            
            $file->move(public_path('img/siswa'), $nama_file);
            $data['image'] = $nama_file;
        }

        // 1. Simpan Data Siswa ke DB
        $siswa = Siswa::create($data);

        // 2. LOGIC SINKRONISASI ALAT LAIN
        // Mengecek apakah data ini asalnya dari form yang dibawa oleh Inbox Registrasi
        if ($request->has('inbox_id') && $request->inbox_id != '') {
            
            $inbox = \App\Models\FingerprintInbox::find($request->inbox_id);
            
            if ($inbox) {
                // Tandai inbox ini sudah selesai diproses (assigned)
                $inbox->update(['status' => 'assigned']);

                // Cari SEMUA alat lain (kecuali alat tempat dia scan/daftar pertama kali)
                $alatLain = \App\Models\Device::where('id_device', '!=', $inbox->id_device)->get();
                
                // Buatkan tugas (Task) untuk masing-masing alat lain agar merekam/mendownload ID jari ini
                foreach ($alatLain as $alat) {
                    \App\Models\DeviceTask::create([
                        'id_device' => $alat->id_device,
                        'fingerprint_id' => $siswa->fingerprint_id,
                        'action' => 'enroll',
                        'status' => 'pending'
                    ]);
                }
            }
        }

        return redirect()->route('admin.siswa.index')->with('success', 'Data siswa berhasil ditambahkan. Tugas sinkronisasi sidik jari ke alat lain telah dibuat (jika ada).');
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
            if ($siswa->image && File::exists(public_path('img/siswa/' . $siswa->image))) {
                File::delete(public_path('img/siswa/' . $siswa->image));
            }

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
        if ($siswa->image && File::exists(public_path('img/siswa/' . $siswa->image))) {
            File::delete(public_path('img/siswa/' . $siswa->image));
        }

        $siswa->delete();
        return redirect()->route('admin.siswa.index')->with('success', 'Data siswa berhasil dihapus.');
    }

    // ==========================================
    // FITUR EXCEL: DOWNLOAD TEMPLATE
    // ==========================================
    public function downloadTemplate()
    {
        return Excel::download(new SiswaTemplateExport, 'Template_Import_Siswa.xlsx');
    }

    // ==========================================
    // FITUR EXCEL: UPLOAD & IMPORT DATA
    // ==========================================
    public function importExcel(Request $request)
    {
        $request->validate([
            'file_excel' => 'required|mimes:xlsx,xls,csv|max:5120', // Maks 5MB
        ]);

        try {
            Excel::import(new SiswaImport, $request->file('file_excel'));
            
            // JIKA SUKSES (Tidak ada duplikasi)
            return redirect()->back()->with('success', 'Data siswa dari Excel berhasil di-import!');
            
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            // JIKA GAGAL (Ada duplikasi / error validasi di Strategi A)
            $failures = $e->failures();
            $pesanError = "Gagal Import! ";
            
            foreach ($failures as $failure) {
                $pesanError .= "Baris ke-{$failure->row()}: " . implode(', ', $failure->errors()) . " ";
            }

            return redirect()->back()->with('error', $pesanError);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan sistem: ' . $e->getMessage());
        }
    }
}