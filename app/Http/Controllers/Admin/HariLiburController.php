<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HariLibur;
use App\Services\AcademicCalendarService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class HariLiburController extends Controller
{
    protected $calendarService;

    public function __construct(AcademicCalendarService $calendarService)
    {
        $this->calendarService = $calendarService;
    }

    public function index(Request $request)
    {
        $query = HariLibur::query();

        // Filter by year if selected, otherwise show current/upcoming or all
        $selectedYear = $request->get('year', Carbon::now()->year);
        $query->where(function ($q) use ($selectedYear) {
            $q->whereYear('tanggal_mulai', $selectedYear)
              ->orWhereYear('tanggal_selesai', $selectedYear);
        });

        if ($request->has('search') && $request->search != '') {
            $query->where('nama', 'like', '%' . $request->search . '%');
        }

        $hari_libur = $query->orderBy('tanggal_mulai', 'asc')
            ->paginate(15)
            ->withQueryString();

        return view('admin.hari-libur.index', compact('hari_libur', 'selectedYear'));
    }

    public function create()
    {
        return view('admin.hari-libur.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'jenis' => 'required|in:nasional,sekolah',
            'keterangan' => 'nullable|string',
        ]);

        HariLibur::create($request->all());

        return redirect()->route('admin.hari-libur.index')->with('success', 'Hari libur berhasil ditambahkan.');
    }

    public function edit(HariLibur $hariLibur)
    {
        return view('admin.hari-libur.edit', compact('hariLibur'));
    }

    public function update(Request $request, HariLibur $hariLibur)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'jenis' => 'required|in:nasional,sekolah',
            'keterangan' => 'nullable|string',
        ]);

        $hariLibur->update($request->all());

        return redirect()->route('admin.hari-libur.index')->with('success', 'Hari libur berhasil diperbarui.');
    }

    public function destroy(HariLibur $hariLibur)
    {
        $hariLibur->delete();

        return redirect()->route('admin.hari-libur.index')->with('success', 'Hari libur berhasil dihapus.');
    }

    public function sync(Request $request)
    {
        $request->validate([
            'year' => 'required|integer|min:2020|max:2030',
        ]);

        $year = $request->input('year');

        try {
            $count = $this->calendarService->syncNationalHolidays($year);
            return redirect()->route('admin.hari-libur.index', ['year' => $year])
                ->with('success', "Berhasil menyinkronkan {$count} hari libur nasional untuk tahun {$year}.");
        } catch (\Throwable $e) {
            return redirect()->route('admin.hari-libur.index', ['year' => $year])
                ->with('error', "Gagal sinkronisasi: " . $e->getMessage());
        }
    }
}
