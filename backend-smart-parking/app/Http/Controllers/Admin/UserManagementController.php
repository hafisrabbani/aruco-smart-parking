<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserManagementController extends Controller
{
    public function index()
    {
        // Ambil semua user dengan jumlah parkir dan status aktif
        $users = User::withCount('parkings')
            ->with(['parkings' => function ($q) {
                $q->whereNull('exit_time');
            }])
            ->get();

        return view('admin.user', compact('users'));
    }
}
