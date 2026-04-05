<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();

        // Fitur Pencarian (Nama atau Username)
        if ($request->has('search') && $request->search != '') {
            $query->where(function($q) use ($request) {
                $q->where('nama', 'like', '%' . $request->search . '%')
                  ->orWhere('username', 'like', '%' . $request->search . '%');
            });
        }

        // Fitur Filter Role (admin, guru, dll)
        if ($request->has('role') && $request->role != '') {
            $query->where('role', $request->role);
        }

        // Urutkan dari yang terbaru, pagination 15 per halaman
        $users = $query->latest()->paginate(15);
        
        return view('admin.user.index', compact('users'));
    }
}