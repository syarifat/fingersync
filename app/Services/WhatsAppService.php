<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Models\LogWhatsapp;

class WhatsAppService
{
    public static function send($target, $message, $id_siswa = null)
    {
        // Mengambil token dari file .env secara aman
        $token = env('FONNTE_TOKEN');
        
        if(!$target) return false;

        try {
            $response = Http::withHeaders([
                'Authorization' => $token,
            ])->post('https://api.fonnte.com/send', [
                'target' => $target,
                'message' => $message,
                'countryCode' => '62', // Default Indonesia
            ]);

            $isSuccess = $response->successful();

            // Log the message
            LogWhatsapp::create([
                'id_siswa' => $id_siswa,
                'no_wa' => $target,
                'pesan' => $message,
                'status' => $isSuccess ? 'Berhasil' : 'Gagal'
            ]);

            return $isSuccess;
        } catch (\Throwable $th) {
            LogWhatsapp::create([
                'id_siswa' => $id_siswa,
                'no_wa' => $target,
                'pesan' => $message,
                'status' => 'Gagal'
            ]);
            return false;
        }
    }
}