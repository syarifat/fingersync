<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Models\Ruangan;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DeviceController extends Controller
{
    public function index()
    {
        // Eager load 'ruangan' agar tidak n+1 problem
        $devices = Device::with('ruangan')->latest()->paginate(10);
        return view('admin.device.index', compact('devices'));
    }

    public function create()
    {
        $ruangan = Ruangan::all(); // Untuk dropdown pilihan lokasi
        return view('admin.device.create', compact('ruangan'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_device' => 'required|string|unique:device,id_device', // ID Hardware harus unik
            'id_ruangan' => 'required|exists:ruangan,id',
        ]);

        Device::create([
            'id_device' => $request->id_device,
            'id_ruangan' => $request->id_ruangan,
            'status' => 'Offline' // Default status awal
        ]);

        return redirect()->route('admin.device.index')
            ->with('success', 'Perangkat IoT berhasil didaftarkan.');
    }

    public function edit(Device $device)
    {
        $ruangan = Ruangan::all();
        return view('admin.device.edit', compact('device', 'ruangan'));
    }

    public function update(Request $request, Device $device)
    {
        $request->validate([
            'id_device' => ['required', Rule::unique('device')->ignore($device->id)],
            'id_ruangan' => 'required|exists:ruangan,id',
            'status' => 'required|in:Online,Offline,Maintenance'
        ]);

        $device->update($request->all());

        return redirect()->route('admin.device.index')
            ->with('success', 'Konfigurasi perangkat berhasil diperbarui.');
    }

    public function destroy(Device $device)
    {
        $device->delete();
        return redirect()->route('admin.device.index')
            ->with('success', 'Perangkat berhasil dihapus dari sistem.');
    }
}