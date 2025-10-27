<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Parking;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthAdminController extends Controller
{
    public function showLoginForm()
    {
        return view('admin.auth');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $admin = Admin::where('email', $request->email)->first();

        if (!$admin || !Hash::check($request->password, $admin->password)) {
            return back()->withErrors(['email' => 'Invalid credentials'])->withInput();
        }

        session(['admin' => $admin]);
        return redirect()->route('admin.dashboard');
    }

    public function dashboard()
    {
        $totalParking = Parking::whereNull('exit_time')->count();
        $capacity = Setting::first()->parking_capacity ?? 0;
        $admin = session('admin');
        return view('admin.dashboard', compact('admin', 'totalParking', 'capacity'));
    }

    public function logout()
    {
        session()->forget('admin');
        return redirect()->route('admin.login');
    }
}
