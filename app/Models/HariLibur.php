<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HariLibur extends Model
{
    protected $table = 'hari_liburs';
    protected $fillable = ['nama', 'tanggal_mulai', 'tanggal_selesai', 'jenis', 'keterangan'];

    /**
     * Check if a specific date is registered as a holiday (school or national)
     *
     * @param string $date format: YYYY-MM-DD
     * @return bool
     */
    public static function isHoliday($date)
    {
        return self::where('tanggal_mulai', '<=', $date)
            ->where('tanggal_selesai', '>=', $date)
            ->exists();
    }

    /**
     * Get the holiday name on a specific date (if any)
     *
     * @param string $date format: YYYY-MM-DD
     * @return string|null
     */
    public static function getHolidayName($date)
    {
        return self::where('tanggal_mulai', '<=', $date)
            ->where('tanggal_selesai', '>=', $date)
            ->value('nama');
    }
}
