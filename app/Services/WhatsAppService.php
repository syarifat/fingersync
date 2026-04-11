<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class WhatsAppService
{
    public static function send($target, $message)
    {
        // Mengambil token dari file .env secara aman
        $token = env('FONNTE_TOKEN');
        
        if(!$target) return false;

        $response = Http::withHeaders([
            'Authorization' => $token,
        ])->post('https://api.fonnte.com/send', [
            'target' => $target,
            'message' => $message,
            'countryCode' => '62', // Default Indonesia
        ]);

        return $response->successful();
    }
}