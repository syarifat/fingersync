<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;

class AffectedRecordsController extends Controller
{
    public function check(Request $request)
    {
        $url = $request->input('url');
        $method = strtoupper($request->input('method', 'POST'));

        // Deteksi override method dari form data (misal input _method = DELETE atau PUT)
        $matchMethod = $method;
        if ($request->has('form_data')) {
            parse_str($request->input('form_data'), $formData);
            if (isset($formData['_method'])) {
                $matchMethod = strtoupper($formData['_method']);
            }
        }

        if (empty($url)) {
            return response()->json(['affected' => false, 'list' => []]);
        }

        try {
            // Cocokkan rute menggunakan request palsu
            $fakeRequest = Request::create($url, $matchMethod);
            $route = Route::getRoutes()->match($fakeRequest);
            $routeName = $route->getName();
            $parameters = $route->parameters();
        } catch (\Throwable $e) {
            return response()->json(['affected' => false, 'list' => []]);
        }

        // 1. Kasus Khusus: Plotting Guru
        if ($routeName === 'admin.rombel-mata-pelajaran.storeManage') {
            $activeYear = session('tahun_ajar_id');
            $id = $parameters['id_kelas'] ?? null;
            if ($id && $activeYear) {
                $plottingIds = DB::table('rombel_mata_pelajaran')
                    ->where('id_kelas', $id)
                    ->where('id_tahun_ajar', $activeYear)
                    ->pluck('id')
                    ->toArray();

                $affectedList = $this->getAffectedCounts('rombel_mata_pelajaran', $plottingIds);
                return response()->json([
                    'affected' => !empty($affectedList),
                    'list' => array_values($affectedList)
                ]);
            }
        }

        // 2. Kasus Khusus: Jadwal Pelajaran
        if ($routeName === 'admin.rombel-jadwal.storeManage') {
            $activeYear = session('tahun_ajar_id');
            $id = $parameters['id_kelas'] ?? null;
            if ($id && $activeYear) {
                $plottingIds = DB::table('rombel_mata_pelajaran')
                    ->where('id_kelas', $id)
                    ->where('id_tahun_ajar', $activeYear)
                    ->pluck('id')
                    ->toArray();
                $jadwalLamaIds = DB::table('rombel_jadwal_pelajaran')
                    ->whereIn('id_rombel_mata_pelajaran', $plottingIds)
                    ->pluck('id')
                    ->toArray();

                $affectedList = $this->getAffectedCounts('rombel_jadwal_pelajaran', $jadwalLamaIds);
                return response()->json([
                    'affected' => !empty($affectedList),
                    'list' => array_values($affectedList)
                ]);
            }
        }

        // 3. Kasus Umum: Delete / Update Resource Standard
        $paramToTable = [
            'siswa' => 'siswa',
            'guru' => 'guru',
            'kela' => 'kelas',
            'kelas' => 'kelas',
            'ruangan' => 'ruangan',
            'jurusan' => 'jurusan',
            'mata_pelajaran' => 'mata_pelajaran',
            'device' => 'device',
            'tahun_ajar' => 'tahun_ajar',
            'user' => 'users',
        ];

        if (!empty($parameters)) {
            $paramName = key($parameters);
            $paramValue = current($parameters);
            $id = is_object($paramValue) ? $paramValue->getKey() : $paramValue;

            $tableName = $paramToTable[$paramName] ?? null;

            if ($tableName && $id && ($matchMethod === 'DELETE' || $method === 'DELETE')) {
                $affectedList = $this->getAffectedCounts($tableName, [$id]);
                return response()->json([
                    'affected' => !empty($affectedList),
                    'list' => array_values($affectedList)
                ]);
            }
        }

        return response()->json(['affected' => false, 'list' => []]);
    }

    private function getAffectedCounts($tableName, $ids, &$visited = [])
    {
        if (empty($ids)) {
            return [];
        }

        if (in_array($tableName, $visited)) {
            return [];
        }
        $visited[] = $tableName;

        $dbName = DB::connection()->getDatabaseName();

        // Cari relasi kunci asing yang merujuk ke tabel target
        $foreignKeys = DB::select("
            SELECT 
                TABLE_NAME as child_table, 
                COLUMN_NAME as child_column
            FROM 
                information_schema.KEY_COLUMN_USAGE 
            WHERE 
                REFERENCED_TABLE_SCHEMA = ? 
                AND REFERENCED_TABLE_NAME = ?
        ", [$dbName, $tableName]);

        $results = [];

        foreach ($foreignKeys as $fk) {
            $childTable = $fk->child_table;
            $childColumn = $fk->child_column;

            $count = DB::table($childTable)
                ->whereIn($childColumn, $ids)
                ->count();

            if ($count > 0) {
                $childIds = DB::table($childTable)
                    ->whereIn($childColumn, $ids)
                    ->pluck('id')
                    ->toArray();

                $label = $this->getHumanLabel($childTable);
                
                if (isset($results[$childTable])) {
                    $results[$childTable]['count'] += $count;
                } else {
                    $results[$childTable] = [
                        'table' => $childTable,
                        'label' => $label,
                        'count' => $count
                    ];
                }

                // Penelusuran rekursif untuk tabel anak dari tabel anak ini
                $childRecs = $this->getAffectedCounts($childTable, $childIds, $visited);
                foreach ($childRecs as $childTableRec => $data) {
                    if (isset($results[$childTableRec])) {
                        $results[$childTableRec]['count'] += $data['count'];
                    } else {
                        $results[$childTableRec] = $data;
                    }
                }
            }
        }

        return $results;
    }

    private function getHumanLabel($tableName)
    {
        $labels = [
            'siswa' => 'Data Siswa',
            'guru' => 'Data Guru/Pendidik',
            'kelas' => 'Data Kelas',
            'jurusan' => 'Data Jurusan',
            'mata_pelajaran' => 'Data Mata Pelajaran',
            'tahun_ajar' => 'Data Tahun Ajaran',
            'ruangan' => 'Data Ruangan',
            'device' => 'Perangkat Scanner (Device)',
            'rombel_kelas' => 'Keanggotaan Kelas Siswa (Rombel)',
            'rombel_mata_pelajaran' => 'Plotting Guru Mata Pelajaran',
            'rombel_jadwal_pelajaran' => 'Jadwal Pelajaran',
            'presensi' => 'Riwayat Kehadiran (Presensi)',
            'log_whatsapps' => 'Log Pengiriman WhatsApp',
            'fingerprint_inboxes' => 'Inbox Registrasi Sidik Jari',
            'device_tasks' => 'Antrean Tugas Perangkat (Device Task)',
            'users' => 'Akun Pengguna (User)',
        ];

        return $labels[$tableName] ?? ucfirst(str_replace('_', ' ', $tableName));
    }
}
