<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Guru;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AktivasiGuruController extends Controller
{
    /**
     * Menampilkan form aktivasi / set password
     */
    public function create()
    {
        return view('auth.aktivasi-guru');
    }

    /**
     * Memproses penyimpanan password baru
     */
    public function store(Request $request)
    {
        $request->validate([
            'username' => 'required|string|exists:users,username',
            'password' => 'required|string|min:6|confirmed', // Harus ada field password_confirmation di view
        ]);

        // 1. Cari User berdasarkan Username
        $user = User::where('username', $request->username)->first();

        // 2. Validasi Keamanan:
        // Pastikan role-nya adalah Guru
        if ($user->role !== 'guru') {
            throw ValidationException::withMessages([
                'username' => __('Username ini bukan akun Guru.'),
            ]);
        }

        // Pastikan password-nya masih NULL (Belum pernah diaktivasi)
        if ($user->password !== null) {
            throw ValidationException::withMessages([
                'username' => __('Akun ini sudah aktif. Silakan login menggunakan password Anda.'),
            ]);
        }

        // 3. Update Password di Tabel Users (Hashed)
        $user->update([
            'password' => Hash::make($request->password),
        ]);

        // 4. Update Password di Tabel Guru (Plain text - Sesuai request struktur database Anda)
        // Cari data guru yang terhubung dengan user ini
        $guru = Guru::where('user_id', $user->id)->first();
        if ($guru) {
            $guru->update([
                'password' => $request->password,
                'status' => 'Aktif' // Opsional: Pastikan status aktif setelah set password
            ]);
        }

        // 5. Redirect ke Login dengan pesan sukses
        return redirect()->route('login')->with('status', 'Aktivasi berhasil! Silakan login dengan password baru Anda.');
    }
}