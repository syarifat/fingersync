<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\RombelMataPelajaran;
use App\Models\RombelJadwalPelajaran; // Sesuaikan nama model jadwal Anda
use Illuminate\Validation\Validator;

class StoreJadwalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_rombel_mata_pelajaran' => 'required|exists:rombel_mata_pelajaran,id',
            'id_ruangan' => 'required|exists:ruangan,id',
            'hari' => 'required|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu',
            'jam_mulai' => 'required|date_format:H:i',
            'jam_selesai' => 'required|date_format:H:i|after:jam_mulai',
        ];
    }

    // DISINI LOGIC UTAMA BENTROKNYA
    public function withValidator(Validator $validator)
    {
        $validator->after(function ($validator) {
            // Ambil data input
            $hari = $this->hari;
            $jamMulai = $this->jam_mulai; // Contoh: 07:00
            $jamSelesai = $this->jam_selesai; // Contoh: 08:30
            $ruanganId = $this->id_ruangan;
            $rombelMapelId = $this->id_rombel_mata_pelajaran;

            // Ambil Data Guru & Kelas dari Rombel Mapel yang dipilih
            $targetRombel = RombelMataPelajaran::find($rombelMapelId);
            
            if (!$targetRombel) return; // Skip jika data gak ada (udah dihandle rules)

            $guruId = $targetRombel->id_guru;
            $kelasId = $targetRombel->id_kelas;
            $tahunAjarId = $targetRombel->id_tahun_ajar;

            // --- FUNGSI QUERY CEK BENTROK (REUSABLE) ---
            // Logic: Jadwal Baru BENTROK jika:
            // (Mulai Baru < Selesai Lama) AND (Selesai Baru > Mulai Lama)
            $cekBentrok = function ($query) use ($hari, $jamMulai, $jamSelesai, $tahunAjarId) {
                $query->where('hari', $hari)
                      ->whereHas('rombelMataPelajaran', function($q) use ($tahunAjarId){
                          $q->where('id_tahun_ajar', $tahunAjarId);
                      })
                      ->where('jam_mulai', '<', $jamSelesai) 
                      ->where('jam_selesai', '>', $jamMulai);
                
                // PENTING: Jika sedang EDIT, abaikan ID jadwal diri sendiri
                if ($this->route('jadwal')) { 
                    $query->where('id', '!=', $this->route('jadwal')->id);
                }
            };

            // 1. CEK BENTROK RUANGAN
            $ruanganTerpakai = RombelJadwalPelajaran::query()
                ->where('id_ruangan', $ruanganId)
                ->where($cekBentrok)
                ->exists();

            if ($ruanganTerpakai) {
                $validator->errors()->add('id_ruangan', 'Ruangan ini sudah terpakai di jam tersebut!');
            }

            // 2. CEK BENTROK GURU
            // Kita cari jadwal lain dimana gurunya sama
            $guruSibuk = RombelJadwalPelajaran::query()
                ->whereHas('rombelMataPelajaran', function ($q) use ($guruId) {
                    $q->where('id_guru', $guruId);
                })
                ->where($cekBentrok)
                ->exists();

            if ($guruSibuk) {
                $validator->errors()->add('id_rombel_mata_pelajaran', 'Guru yang mengajar mapel ini sedang mengajar di kelas lain pada jam tersebut!');
            }

            // 3. CEK BENTROK KELAS (SISWA)
            // Kita cari jadwal lain dimana kelasnya sama
            $kelasSibuk = RombelJadwalPelajaran::query()
                ->whereHas('rombelMataPelajaran', function ($q) use ($kelasId) {
                    $q->where('id_kelas', $kelasId);
                })
                ->where($cekBentrok)
                ->exists();

            if ($kelasSibuk) {
                $validator->errors()->add('jam_mulai', 'Kelas ini sudah ada jadwal pelajaran lain di jam tersebut!');
            }
        });
    }
}