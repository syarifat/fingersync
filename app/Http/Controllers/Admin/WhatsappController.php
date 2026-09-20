<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\LogWhatsapp;
use App\Models\Kelas;
use App\Services\WhatsAppService;

class WhatsappController extends Controller
{
    public function index(Request $request)
    {
        // 1. Ambil Status Koneksi dari Baileys Service
        $deviceInfo = WhatsAppService::getStatus();

        $waStatus = $deviceInfo['status'] ?? 'disconnected';
        $waName = $deviceInfo['name'] ?? '-';
        $waPhone = $deviceInfo['phone'] ?? '-';
        $waQr = $deviceInfo['qr'] ?? null;
        $pairingCode = $deviceInfo['pairingCode'] ?? null;

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
            'waStatus', 'waName', 'waPhone', 'waQr', 'pairingCode',
            'logs', 'kelasList'
        ));
    }

    /**
     * Endpoint API status koneksi realtime (AJAX polling)
     */
    public function statusAjax()
    {
        $status = WhatsAppService::getStatus();
        return response()->json($status);
    }

    /**
     * Request Pairing Code via nomor telepon
     */
    public function pairCode(Request $request)
    {
        $request->validate([
            'phone' => 'required|string|min:8|max:20',
        ]);

        $res = WhatsAppService::requestPairCode($request->phone);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json($res);
        }

        if (isset($res['success']) && $res['success'] === true) {
            return redirect()->route('admin.whatsapp.index')
                ->with('success', 'Kode Pairing berhasil digenerate: ' . ($res['code'] ?? ''));
        }

        return redirect()->route('admin.whatsapp.index')
            ->with('error', $res['message'] ?? 'Gagal membuat kode pairing.');
    }

    /**
     * Logout / Reset sesi WhatsApp Baileys
     */
    public function logout(Request $request)
    {
        $res = WhatsAppService::logout();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json($res);
        }

        return redirect()->route('admin.whatsapp.index')
            ->with('success', 'Sesi WhatsApp berhasil diputus. Silakan scan QR baru atau gunakan Pairing Code.');
    }

    /**
     * Restart koneksi Baileys
     */
    public function restart(Request $request)
    {
        $res = WhatsAppService::restart();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json($res);
        }

        return redirect()->route('admin.whatsapp.index')
            ->with('success', 'Koneksi Baileys sedang direstart.');
    }
}
