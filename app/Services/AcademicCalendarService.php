<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Models\HariLibur;
use Exception;

class AcademicCalendarService
{
    /**
     * Synchronize national holidays for a given year from the external API
     *
     * @param int $year
     * @return int Number of successfully synchronized holidays
     * @throws Exception
     */
    public function syncNationalHolidays($year)
    {
        $url = "https://api-hari-libur.vercel.app/api?year={$year}";

        try {
            $response = Http::timeout(10)->get($url);

            if (!$response->successful()) {
                throw new Exception("Gagal menghubungi API Hari Libur. Status: " . $response->status());
            }

            $body = $response->json();

            if (!isset($body['status']) || $body['status'] !== 'success' || !isset($body['data'])) {
                throw new Exception("API Hari Libur mengembalikan format data yang salah.");
            }

            $holidays = $body['data'];
            $count = 0;

            foreach ($holidays as $item) {
                if (empty($item['date']) || empty($item['description'])) {
                    continue;
                }

                // API returns single dates, so start date and end date are the same.
                HariLibur::updateOrCreate(
                    [
                        'tanggal_mulai' => $item['date'],
                        'tanggal_selesai' => $item['date'],
                        'jenis' => 'nasional',
                    ],
                    [
                        'nama' => $item['description'],
                    ]
                );

                $count++;
            }

            return $count;
        } catch (\Throwable $th) {
            throw new Exception("Terjadi kesalahan saat sinkronisasi kalender akademik: " . $th->getMessage());
        }
    }
}
