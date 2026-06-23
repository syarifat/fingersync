<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\LogWhatsapp;
use App\Models\Kelas;

class WhatsappController extends Controller
{
    public function index(Request $request)
    {
        // 1. Ambil Status Koneksi dari Fonnte
        $token = env('FONNTE_TOKEN');
        $fonnteStatus = 'disconnected';
        $fonnteName = '-';
        $fonnteDevice = '-';
        $fonnteQr = null;

        try {
            $response = Http::withHeaders([
                'Authorization' => $token,
            ])->post('https://api.fonnte.com/device');

            if ($response->successful()) {
                $data = $response->json();
                $fonnteStatus = $data['device_status'] ?? 'disconnected';
                $fonnteName = $data['name'] ?? '-';
                $fonnteDevice = $data['device'] ?? '-';
                if ($fonnteStatus == 'disconnect') { // Fonnte returns 'disconnect' not 'disconnected'
                    $fonnteStatus = 'disconnected';
                    $fonnteQr = $data['qr'] ?? null;
                }
            }
        } catch (\Throwable $th) {
            $fonnteStatus = 'error';
        }

        // 2. Query Log WhatsApp
        $query = LogWhatsapp::with(['siswa.rombelKelas.kelas']);

        // Filter Pencarian (Nama / No WA)
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('no_wa', 'like', "%{$search}%")
                  ->orWhereHas('siswa', function($qSiswa) use ($search) {
                      $qSiswa->where('nama', 'like', "%{$search}%");
                  });
            });
        }

        // Filter Kelas
        if ($request->has('kelas_id') && $request->kelas_id != '') {
            $kelas_id = $request->kelas_id;
            $kelas = Kelas::find($kelas_id);
            $nama_kelas = $kelas ? $kelas->nama : '';

            $query->where(function($q) use ($kelas_id, $nama_kelas) {
                // Untuk log yang memiliki id_siswa
                $q->whereHas('siswa.rombelKelas', function($qRombel) use ($kelas_id) {
                    $qRombel->where('id_kelas', $kelas_id);
                });

                // Untuk log yang tidak memiliki id_siswa (kolektif/grup), cari dari isi pesan
                if ($nama_kelas != '') {
                    $q->orWhere('pesan', 'like', '%Kelas: *' . $nama_kelas . '*%')
                      ->orWhere('pesan', 'like', '%KELAS ' . $nama_kelas . '%');
                }
            });
        }

        // Filter Jenis Pesan
        if ($request->has('jenis') && $request->jenis != '') {
            if ($request->jenis == 'rekap_sore') {
                $query->where('pesan', 'like', '%REKAP PRESENSI HARIAN KELAS%');
            } elseif ($request->jenis == 'absen_pulang') {
                $query->where('pesan', 'like', '%PEMBERITAHUAN PULANG%');
            }
        }

        // Filter Status
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        $logs = $query->latest()->paginate(10)->withQueryString();
        $kelasList = Kelas::orderBy('nama', 'asc')->get();

        return view('admin.whatsapp.index', compact(
            'fonnteStatus', 'fonnteName', 'fonnteDevice', 'fonnteQr', 'logs', 'kelasList'
        ));
    }
}
