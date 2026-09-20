<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Models\LogWhatsapp;

class WhatsAppService
{
    /**
     * Get Baileys base URL from configuration
     */
    protected static function getBaseUrl(): string
    {
        return rtrim(config('services.baileys.url', env('BAILEYS_API_URL', 'http://127.0.0.1:3000')), '/');
    }

    /**
     * Send WhatsApp message to a number or group
     *
     * @param string $target Phone number or Group JID
     * @param string $message Message text
     * @param int|null $id_siswa Siswa ID for logging
     * @return bool
     */
    public static function send($target, $message, $id_siswa = null)
    {
        if (!$target) return false;

        $baseUrl = self::getBaseUrl();

        try {
            $response = Http::timeout(15)->post("{$baseUrl}/api/send-message", [
                'target' => $target,
                'message' => $message,
            ]);

            $isSuccess = $response->successful() && ($response->json('success') === true);

            // Log the message to database safely
            try {
                LogWhatsapp::create([
                    'id_siswa' => $id_siswa,
                    'no_wa' => $target,
                    'pesan' => $message,
                    'status' => $isSuccess ? 'Berhasil' : 'Gagal'
                ]);
            } catch (\Throwable $dbErr) {
                // Ignore database connection error if DB is down
            }

            return $isSuccess;
        } catch (\Throwable $th) {
            try {
                LogWhatsapp::create([
                    'id_siswa' => $id_siswa,
                    'no_wa' => $target,
                    'pesan' => $message,
                    'status' => 'Gagal'
                ]);
            } catch (\Throwable $dbErr) {}
            return false;
        }
    }

    /**
     * Fetch WhatsApp groups connected to Baileys
     *
     * @return array Array of ['id' => string, 'name' => string]
     */
    public static function getGroups()
    {
        $baseUrl = self::getBaseUrl();

        try {
            $response = Http::timeout(10)->get("{$baseUrl}/api/groups");

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['success']) && $data['success'] === true && isset($data['data'])) {
                    return $data['data'];
                }
            }
            return [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Fetch WhatsApp device connection status & QR code from Baileys
     *
     * @return array
     */
    public static function getStatus()
    {
        $baseUrl = self::getBaseUrl();

        try {
            $response = Http::timeout(5)->get("{$baseUrl}/api/status");

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'status' => $data['status'] ?? 'disconnected',
                    'name' => $data['name'] ?? '-',
                    'phone' => $data['phone'] ?? '-',
                    'qr' => $data['qr'] ?? null,
                    'pairingCode' => $data['pairingCode'] ?? null,
                ];
            }

            return [
                'status' => 'error',
                'name' => '-',
                'phone' => '-',
                'qr' => null,
                'pairingCode' => null,
            ];
        } catch (\Throwable $th) {
            return [
                'status' => 'error',
                'name' => '-',
                'phone' => '-',
                'qr' => null,
                'pairingCode' => null,
            ];
        }
    }

    /**
     * Request a Pairing Code for phone number
     *
     * @param string $phone
     * @return array
     */
    public static function requestPairCode($phone)
    {
        $baseUrl = self::getBaseUrl();

        try {
            $response = Http::timeout(15)->post("{$baseUrl}/api/pair-code", [
                'phone' => $phone
            ]);

            return $response->json() ?? ['success' => false, 'message' => 'Respon kosong dari server Baileys'];
        } catch (\Throwable $th) {
            return [
                'success' => false,
                'message' => 'Gagal terhubung ke service Baileys: ' . $th->getMessage()
            ];
        }
    }

    /**
     * Logout and delete Baileys WhatsApp session
     *
     * @return array
     */
    public static function logout()
    {
        $baseUrl = self::getBaseUrl();

        try {
            $response = Http::timeout(10)->post("{$baseUrl}/api/logout");
            return $response->json() ?? ['success' => true];
        } catch (\Throwable $th) {
            return [
                'success' => false,
                'message' => 'Gagal terhubung ke service Baileys: ' . $th->getMessage()
            ];
        }
    }

    /**
     * Restart Baileys socket connection
     *
     * @return array
     */
    public static function restart()
    {
        $baseUrl = self::getBaseUrl();

        try {
            $response = Http::timeout(10)->post("{$baseUrl}/api/restart");
            return $response->json() ?? ['success' => true];
        } catch (\Throwable $th) {
            return [
                'success' => false,
                'message' => 'Gagal restart service Baileys: ' . $th->getMessage()
            ];
        }
    }
}