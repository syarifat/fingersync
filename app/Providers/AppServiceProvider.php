<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Schema;
use App\Models\TahunAjar;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Fix untuk error key length pada MySQL versi lama (opsional tapi aman)
        Schema::defaultStringLength(191);

        // LOGIKA DROPDOWN TAHUN AJAR
        // Kita gunakan try-catch agar saat migrasi awal tidak error "Table not found"
        try {
            // Cek apakah tabel tahun_ajar sudah ada di database
            if (Schema::hasTable('tahun_ajar')) {
                $globalTahunAjar = TahunAjar::orderBy('tahun', 'desc')->orderBy('semester', 'desc')->get();
                
                // Bagikan variable $globalTahunAjar ke SEMUA view (*)
                View::share('globalTahunAjar', $globalTahunAjar);
            }
        } catch (\Exception $e) {
            // Jika error (misal database belum siap), kirim array kosong
            View::share('globalTahunAjar', []);
        }
    }
}
